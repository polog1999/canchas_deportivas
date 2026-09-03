<?php

namespace App\Http\Controllers;

use App\Models\CatalogoTusne;
use App\Models\Distrito;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Transaccion;
use App\Services\NiubizService;
use App\Services\OracleService;
use App\Services\ReservaCheckoutService;
use App\Services\ReservaCorreoService;
use App\Support\CatalogoTusneReserva;
use App\Support\ReservaFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarPagoNiubizController extends Controller
{
    public function __invoke(
        Request $request,
        string $purchaseNumber,
        NiubizService $niubiz
    ): RedirectResponse {

        Log::channel('niubiz')->info(
            "[Verify] ================================================"
        );

        Log::channel('niubiz')->info(
            "[Verify] INICIO PROCESO DE VERIFICACION DE PAGO",
            [
                'purchase_number' => $purchaseNumber,
                'method' => $request->method(),
                'ip' => $request->ip(),
                'has_transaction_token' => $request->filled('transactionToken'),
                'keys' => array_keys($request->all()),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | BUSCAR CHECKOUT O RESERVA LEGACY
        |--------------------------------------------------------------------------
        */

        $checkoutService = app(ReservaCheckoutService::class);
        $checkout = $checkoutService->obtener($purchaseNumber);
        $esCheckout = $checkout !== null;
        $reserva = null;

        if ($esCheckout) {

            Log::channel('niubiz')->info(
                '[Verify] Checkout en caché encontrado',
                [
                    'purchase_number' => $purchaseNumber,
                    'voucher' => $checkout['voucher'] ?? null,
                ]
            );
        } else {

            Log::channel('niubiz')->info(
                "[Verify] Buscando reserva",
                [
                    'purchase_number' => $purchaseNumber,
                ]
            );

            $reserva = Reserva::query()->find($purchaseNumber);

            if (! $reserva) {

                Log::channel('niubiz')->error(
                    "[Verify] RESERVA NO ENCONTRADA",
                    [
                        'purchase_number' => $purchaseNumber,
                    ]
                );

                return redirect('/')->with(
                    'error',
                    'Reserva de pago no encontrada.'
                );
            }

            Log::channel('niubiz')->info(
                "[Verify] Reserva encontrada",
                [
                    'reserva_id' => $reserva->id,
                    'estado' => $reserva->estado,
                    'precio_total' => $reserva->precio_total,
                    'referencia_pago' => $reserva->referencia_pago,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | SI YA ESTA CONFIRMADA
            |--------------------------------------------------------------------------
            */

            if (strtolower((string) $reserva->estado) === 'confirmada') {

                Log::channel('niubiz')->warning(
                    "[Verify] RESERVA YA CONFIRMADA",
                    [
                        'reserva_id' => $reserva->id,
                        'purchase_number' => $purchaseNumber,
                    ]
                );

                return $this->redirectResultado(
                    'exitoso',
                    $reserva
                );
            }
        }

        $contextoId = $reserva?->id ?? $purchaseNumber;

        /*
        |--------------------------------------------------------------------------
        | LOCK DE PROCESAMIENTO
        |--------------------------------------------------------------------------
        */

        Log::channel('niubiz')->info(
            "[Verify] Intentando adquirir lock de pago",
            [
                'contexto_id' => $contextoId,
                'purchase_number' => $purchaseNumber,
                'lock_key' => 'verify_payment_reserva_' . $purchaseNumber,
                'es_checkout' => $esCheckout,
            ]
        );

        $lock = Cache::lock(
            'verify_payment_reserva_' . $purchaseNumber,
            15
        );

        if (! $lock->get()) {

            Log::channel('niubiz')->warning(
                "[Verify] No se pudo adquirir lock. Otro proceso está verificando el pago",
                [
                    'contexto_id' => $contextoId,
                    'purchase_number' => $purchaseNumber,
                ]
            );

            if ($esCheckout) {
                return $this->redirectResultadoSinReserva(
                    'procesando',
                    'El pago ya se está procesando. Espera un momento y revisa el resultado.',
                    $checkout,
                );
            }

            for ($i = 0; $i < 6; $i++) {

                usleep(500000);

                $reserva->refresh();

                Log::channel('niubiz')->info(
                    "[Verify] Esperando procesamiento concurrente",
                    [
                        'reserva_id' => $reserva->id,
                        'intento' => $i + 1,
                        'estado_actual' => $reserva->estado,
                    ]
                );

                if (
                    strtolower((string) $reserva->estado)
                    === 'confirmada'
                ) {

                    Log::channel('niubiz')->info(
                        "[Verify] Reserva confirmada por el proceso concurrente",
                        [
                            'reserva_id' => $reserva->id,
                        ]
                    );

                    return $this->redirectResultado(
                        'exitoso',
                        $reserva
                    );
                }
            }

            Log::channel('niubiz')->warning(
                "[Verify] Tiempo de espera agotado esperando proceso concurrente",
                [
                    'reserva_id' => $reserva->id,
                    'estado_actual' => $reserva->estado,
                ]
            );

            return $this->redirectResultado(
                'procesando',
                $reserva,
                'El pago ya se está procesando. Espera un momento y revisa el resultado.'
            );
        }

        Log::channel('niubiz')->info(
            "[Verify] Lock adquirido correctamente",
            [
                'contexto_id' => $contextoId,
                'purchase_number' => $purchaseNumber,
            ]
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION TOKEN
            |--------------------------------------------------------------------------
            */

            $transactionToken = $request->input(
                'transactionToken'
            );

            if (! $transactionToken) {

                Log::channel('niubiz')->error(
                    '[Verify] FALTA TRANSACTION TOKEN',
                    [
                        'contexto_id' => $contextoId,
                        'purchase_number' => $purchaseNumber,
                    ]
                );

                if ($esCheckout) {
                    return $this->redirectResultadoSinReserva(
                        'error',
                        'Respuesta de pago inválida.',
                        $checkout,
                    );
                }

                return $this->redirectResultado(
                    'error',
                    $reserva,
                    'Respuesta de pago inválida.'
                );
            }

            Log::channel('niubiz')->info(
                '[Verify] Transaction token recibido correctamente',
                [
                    'contexto_id' => $contextoId,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | MONTO
            |--------------------------------------------------------------------------
            */

            $monto = $esCheckout
                ? round((float) $checkout['precio'], 2)
                : round((float) $reserva->precio_total, 2);

            Log::channel('niubiz')->info(
                '[Verify] Monto calculado',
                [
                    'contexto_id' => $contextoId,
                    'monto' => $monto,
                    'es_checkout' => $esCheckout,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | AUTORIZAR PAGO EN NIUBIZ
            |--------------------------------------------------------------------------
            */

            Log::channel('niubiz')->info(
                '[Verify] Iniciando autorización de transacción en Niubiz',
                [
                    'contexto_id' => $contextoId,
                    'purchase_number' => $purchaseNumber,
                    'monto' => $monto,
                ]
            );

            $response = $niubiz->authorizeTransaction(
                $transactionToken,
                $purchaseNumber,
                $monto
            );

            if ($response === null) {

                Log::channel('niubiz')->error(
                    '[Verify] Niubiz devolvió respuesta NULL',
                    [
                        'contexto_id' => $contextoId,
                        'purchase_number' => $purchaseNumber,
                    ]
                );

                if ($esCheckout) {
                    $checkoutService->liberar($purchaseNumber);

                    return $this->redirectResultadoSinReserva(
                        'error',
                        'No se pudo autorizar el pago. Intenta nuevamente.',
                        $checkout,
                    );
                }

                $reserva->update([
                    'estado' => 'pago_fallido'
                ]);

                Log::channel('niubiz')->warning(
                    '[Verify] Reserva marcada como pago_fallido',
                    [
                        'reserva_id' => $reserva->id,
                    ]
                );

                return $this->redirectResultado(
                    'error',
                    $reserva,
                    'No se pudo autorizar el pago. Intenta nuevamente.'
                );
            }

            Log::channel('niubiz')->info(
                '[Verify] Respuesta recibida de Niubiz',
                [
                    'contexto_id' => $contextoId,
                    'purchase_number' => $purchaseNumber,
                    'status' => data_get($response, 'dataMap.STATUS'),
                    'action_code' => data_get($response, 'dataMap.ACTION_CODE'),
                    'es_checkout' => $esCheckout,
                ]
            );

            $isAuthorized = data_get(
                $response,
                'dataMap.STATUS'
            );

            $actionCode =
                data_get(
                    $response,
                    'dataMap.ACTION_CODE'
                )
                ?? data_get(
                    $response,
                    'data.ACTION_CODE'
                )
                ?? data_get(
                    $response,
                    'ACTION_CODE'
                );

            $transactionId =
                data_get(
                    $response,
                    'order.transactionId'
                )
                ?? data_get(
                    $response,
                    'dataMap.TRANSACTION_ID'
                )
                ?? (
                    'NIUBIZ-'
                    . $purchaseNumber
                    . '-'
                    . now()->format('His')
                );

            $authCode =
                data_get(
                    $response,
                    'order.authorizationCode'
                )
                ?? data_get(
                    $response,
                    'dataMap.AUTHORIZATION_CODE'
                );

            $brand =
                data_get(
                    $response,
                    'dataMap.BRAND'
                )
                ?? data_get(
                    $response,
                    'data.BRAND'
                );

            $card =
                data_get(
                    $response,
                    'dataMap.CARD'
                )
                ?? data_get(
                    $response,
                    'data.CARD'
                );

            $amount =
                data_get(
                    $response,
                    'order.amount'
                )
                ?? data_get(
                    $response,
                    'dataMap.AMOUNT'
                )
                ?? $monto;

            Log::channel('niubiz')->info(
                '[Verify] Datos de respuesta Niubiz procesados',
                [
                    'contexto_id' => $contextoId,
                    'purchase_number' => $purchaseNumber,
                    'status' => $isAuthorized,
                    'action_code' => $actionCode,
                    'transaction_id' => $transactionId,
                    'authorization_code' => $authCode,
                    'brand' => $brand,
                    'card' => $card,
                    'amount' => $amount,
                    'es_checkout' => $esCheckout,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | PAGO AUTORIZADO
            |--------------------------------------------------------------------------
            */

            if (NiubizService::pagoAutorizado($response)) {

                Log::channel('niubiz')->info(
                    "[Verify] ================================================"
                );

                Log::channel('niubiz')->info(
                    "[Verify] PAGO AUTORIZADO POR NIUBIZ",
                    [
                        'contexto_id' => $contextoId,
                        'purchase_number' => $purchaseNumber,
                        'transaction_id' => $transactionId,
                        'authorization_code' => $authCode,
                        'amount' => $amount,
                        'es_checkout' => $esCheckout,
                    ]
                );

                $pagoRegistrado = null;
                $pago = null;
                $checkoutMaterializado = false;

                if ($esCheckout) {
                    try {
                        $materializado = $checkoutService->materializar($checkout, [
                            'transaction_id' => $transactionId,
                            'auth_code' => $authCode,
                            'brand' => $brand,
                            'card' => $card,
                            'amount' => $amount,
                            'response' => $response,
                        ]);
                    } catch (\Throwable $e) {
                        Log::channel('niubiz')->error(
                            '[Verify] Error al materializar checkout autorizado',
                            [
                                'purchase_number' => $purchaseNumber,
                                'mensaje' => $e->getMessage(),
                            ]
                        );

                        return $this->redirectResultadoSinReserva(
                            'error',
                            'El pago fue autorizado pero no se pudo registrar la reserva. Contacta a soporte con tu voucher.',
                            $checkout,
                        );
                    }

                    $reserva = $materializado['reserva'];
                    $pagoRegistrado = $materializado['pago'];
                    $pago = $pagoRegistrado;
                    $meta = $materializado['meta'];
                    $usuarioNuevo = $materializado['es_usuario_nuevo'];
                    $usuarioLogin = $materializado['usuario_login'];
                    $clavePlana = $materializado['clave_plana'];
                    Auth::login($materializado['usuario']);
                    $checkoutMaterializado = true;

                    Log::channel('niubiz')->info(
                        '[Verify] Checkout materializado en BD',
                        [
                            'reserva_id' => $reserva->id,
                            'pago_id' => $pago->id,
                            'usuario_id' => $materializado['usuario']->id,
                        ]
                    );
                } else {
                    $meta = session(
                        'pago_meta',
                        []
                    );

                    $usuarioNuevo = (bool) session(
                        'pago_usuario_nuevo',
                        false
                    );

                    $usuarioLogin = session(
                        'pago_usuario_login'
                    );

                    $clavePlana = session(
                        'pago_clave_plana'
                    );

                    Log::channel('niubiz')->info(
                        '[Verify] Metadata de pago obtenida de sesión',
                        [
                            'reserva_id' => $reserva->id,
                            'usuario_nuevo' => $usuarioNuevo,
                            'usuario_login_presente' => is_string($usuarioLogin),
                            'clave_presente' => is_string($clavePlana),
                            'catalogo_tusne_id' => CatalogoTusneReserva::idDesdeMeta($meta),
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | GUARDAR RESERVA + TRANSACCION + PAGO (LEGACY)
                    |--------------------------------------------------------------------------
                    */

                    Log::channel('niubiz')->info(
                        '[Verify] Iniciando transacción local para reserva, transacción y pago',
                        [
                            'reserva_id' => $reserva->id,
                        ]
                    );

                    $pago = DB::transaction(
                        function () use (
                            $reserva,
                            $response,
                            $transactionId,
                            $authCode,
                            $brand,
                            $card,
                            $amount,
                            $meta,
                            &$pagoRegistrado,
                        ) {

                            Log::channel('niubiz')->info(
                                '[Verify] Dentro de transacción DB',
                                [
                                    'reserva_id' => $reserva->id,
                                ]
                            );

                            // Bloqueo pesimista a nivel de base de datos para evitar pagos concurrentes
                            $reserva = Reserva::query()
                                ->whereKey($reserva->id)
                                ->lockForUpdate()
                                ->first();

                            if (
                                strtolower(
                                    (string) $reserva->estado
                                ) === 'confirmada'
                            ) {

                                Log::channel('niubiz')->warning(
                                    '[Verify] La reserva ya estaba confirmada dentro de la transacción',
                                    [
                                        'reserva_id' => $reserva->id,
                                    ]
                                );

                                $pagoExistente = Pago::whereHas(
                                    'transaccion',
                                    function ($query) use ($reserva) {
                                        $query->where(
                                            'reserva_id',
                                            $reserva->id
                                        );
                                    }
                                )
                                    ->latest('id')
                                    ->first();

                                $pagoRegistrado = $pagoExistente;

                                Log::channel('niubiz')->info(
                                    '[Verify] Pago existente recuperado',
                                    [
                                        'reserva_id' => $reserva->id,
                                        'pago_id' => $pagoExistente?->id,
                                    ]
                                );

                                return $pagoExistente;
                            }

                            /*
                        |--------------------------------------------------------------------------
                        | CONFIRMAR RESERVA
                        |--------------------------------------------------------------------------
                        */

                            Log::channel('niubiz')->info(
                                '[Verify] Actualizando reserva a confirmada',
                                [
                                    'reserva_id' => $reserva->id,
                                    'estado_anterior' => $reserva->estado,
                                ]
                            );

                            $reserva->update([
                                'estado' => 'confirmada'
                            ]);

                            Log::channel('niubiz')->info(
                                '[Verify] Reserva confirmada correctamente',
                                [
                                    'reserva_id' => $reserva->id,
                                ]
                            );

                            /*
                        |--------------------------------------------------------------------------
                        | CREAR TRANSACCION
                        |--------------------------------------------------------------------------
                        */

                            Log::channel('niubiz')->info(
                                '[Verify] Creando registro de transacción',
                                [
                                    'reserva_id' => $reserva->id,
                                    'transaction_id' => $transactionId,
                                    'amount' => $amount,
                                ]
                            );

                            $transaccion = Transaccion::create([
                                'reserva_id' => $reserva->id,

                                'transaccion_id' =>
                                (string) $transactionId,

                                'codigo_autorizacion' =>
                                $authCode
                                    ? (string) $authCode
                                    : null,

                                'marca_tarjeta' =>
                                $brand
                                    ? (string) $brand
                                    : null,

                                'tarjeta_enmascarada' =>
                                $card
                                    ? (string) $card
                                    : null,

                                'monto' => round(
                                    (float) $amount,
                                    2
                                ),

                                'estado' => 'Authorized',

                                'respuesta_bruta' => [
                                    'niubiz' => $response,
                                    'voucher' =>
                                    $reserva->referencia_pago,
                                    'meta' => $meta,
                                ],
                            ]);

                            Log::channel('niubiz')->info(
                                '[Verify] Transacción creada correctamente',
                                [
                                    'transaccion_id_local' => $transaccion->id,
                                    'transaction_id_niubiz' => $transactionId,
                                    'reserva_id' => $reserva->id,
                                ]
                            );

                            /*
                        |--------------------------------------------------------------------------
                        | CREAR PAGO
                        |--------------------------------------------------------------------------
                        */

                            Log::channel('niubiz')->info(
                                '[Verify] Creando registro de pago',
                                [
                                    'transaccion_id' => $transaccion->id,
                                    'monto' => $amount,
                                    'catalogo_tusne_id' =>
                                    CatalogoTusneReserva::idDesdeMeta($meta),
                                ]
                            );

                            $pago = Pago::create([
                                'transaccion_id' =>
                                $transaccion->id,

                                'monto' => round(
                                    (float) $amount,
                                    2
                                ),

                                'pagado_en' => now(),

                                'acepto_terminos' =>
                                (bool) session(
                                    'pago_acepto_terminos',
                                    true
                                ),

                                'id_catalogos_tusne' =>
                                CatalogoTusneReserva::idDesdeMeta($meta),
                            ]);

                            $pagoRegistrado = $pago;

                            Log::channel('niubiz')->info(
                                '[Verify] Pago creado correctamente',
                                [
                                    'pago_id' => $pago->id,
                                    'transaccion_id' => $transaccion->id,
                                    'reserva_id' => $reserva->id,
                                    'id_catalogos_tusne' =>
                                    $pago->id_catalogos_tusne,
                                    'monto' => $pago->monto,
                                    'pagado_en' => $pago->pagado_en,
                                ]
                            );

                            return $pago;
                        }
                    );
                }

                Log::channel('niubiz')->info(
                    '[Verify] Transacción local completada correctamente',
                    [
                        'reserva_id' => $reserva->id,
                        'pago_id' => $pago?->id,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | RESERVA CONFIRMADA
                |--------------------------------------------------------------------------
                */

                $reserva->refresh();

                Log::channel('niubiz')->info(
                    '[Verify] Reserva, transacción y pago guardados correctamente',
                    [
                        'reserva_id' => $reserva->id,
                        'purchase_number' => $purchaseNumber,
                        'estado' => $reserva->estado,
                        'pago_id' => $pago?->id,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | OBTENER USUARIO Y PERFIL
                |--------------------------------------------------------------------------
                */

                Log::channel('niubiz')->info(
                    '[Oracle] Iniciando proceso de contribuyente',
                    [
                        'reserva_id' => $reserva->id,
                        'purchase_number' => $purchaseNumber,
                    ]
                );

                $serviceOracle = app(OracleService::class);

                $usuario = auth()->user();

                if (! $usuario) {

                    Log::channel('niubiz')->error(
                        '[Oracle] NO SE PUDO OBTENER EL USUARIO AUTENTICADO',
                        [
                            'reserva_id' => $reserva->id,
                            'purchase_number' => $purchaseNumber,
                        ]
                    );
                } else {

                    Log::channel('niubiz')->info(
                        '[Oracle] Usuario autenticado obtenido',
                        [
                            'usuario_id' => $usuario->id,
                            'reserva_id' => $reserva->id,
                        ]
                    );

                    $perfil = $usuario->perfil;

                    if (! $perfil) {

                        Log::channel('niubiz')->error(
                            '[Oracle] EL USUARIO NO TIENE PERFIL ASOCIADO',
                            [
                                'usuario_id' => $usuario->id,
                                'reserva_id' => $reserva->id,
                            ]
                        );
                    } else {

                        $obj_tipo_documento = $perfil->tipoDocumento;
                        $tipo_documento_doi = trim(
                            (string) $obj_tipo_documento->doi
                        );
                        $num_documento =
                            trim(
                                (string) $perfil->numero_documento
                            );

                        Log::channel('niubiz')->info(
                            '[Oracle] Datos del usuario obtenidos',
                            [
                                'usuario_id' => $usuario->id,
                                'perfil_id' => $perfil->id,
                                'numero_documento' => $num_documento,
                                'cod_contrib_actual' => $perfil->cod_contrib,
                            ]
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | CONSULTAR CONTRIBUYENTE EN ORACLE
                        |--------------------------------------------------------------------------
                        */

                        try {

                            Log::channel('niubiz')->info(
                                '[Oracle] Consultando contribuyente',
                                [
                                    'tipo_documento' => $obj_tipo_documento->abreviatura,
                                    'numero_documento' => $num_documento,
                                    'reserva_id' => $reserva->id,
                                ]
                            );

                            $codContribuyente =
                                $serviceOracle->getCodContribuyente(
                                    $tipo_documento_doi,
                                    $num_documento
                                );

                            Log::channel('niubiz')->info(
                                '[Oracle] Resultado consulta contribuyente',
                                [
                                    'tipo_documento' => $obj_tipo_documento->abreviatura,
                                    'numero_documento' => $num_documento,
                                    'cod_contribuyente' => $codContribuyente,
                                ]
                            );
                        } catch (\Throwable $e) {

                            Log::channel('niubiz')->error(
                                '[Oracle] ERROR CONSULTANDO CONTRIBUYENTE',
                                [
                                    'tipo_documento' => $obj_tipo_documento->abreviatura,
                                    'numero_documento' => $num_documento,
                                    'reserva_id' => $reserva->id,
                                    'mensaje' => $e->getMessage(),
                                    'archivo' => $e->getFile(),
                                    'linea' => $e->getLine(),
                                ]
                            );

                            throw $e;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SI YA EXISTE
                        |--------------------------------------------------------------------------
                        */

                        if ($codContribuyente) {

                            Log::channel('niubiz')->info(
                                '[Oracle] CONTRIBUYENTE YA EXISTE',
                                [
                                    'numero_documento' => $num_documento,
                                    'cod_contribuyente' => $codContribuyente,
                                ]
                            );

                            $perfil->update([
                                'cod_contrib' =>
                                $codContribuyente,
                            ]);

                            Log::channel('niubiz')->info(
                                '[Oracle] COD_CONTRIB ACTUALIZADO EN PERFIL',
                                [
                                    'perfil_id' =>
                                    $perfil->id,
                                    'cod_contrib' =>
                                    $perfil->cod_contrib,
                                ]
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NO EXISTE -> CREAR CONTRIBUYENTE
                        |--------------------------------------------------------------------------
                        */ else {

                            Log::channel('niubiz')->info(
                                '[Oracle] CONTRIBUYENTE NO EXISTE - CREANDO',
                                [
                                    'numero_documento' => $num_documento,
                                ]
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | GENERAR CODIGO
                            |--------------------------------------------------------------------------
                            */

                            $codContribuyente =
                                $this->generarCodigoContribuyente();

                            Log::channel('niubiz')->info(
                                '[Oracle] CODIGO CONTRIBUYENTE GENERADO',
                                [
                                    'cod_contribuyente' =>
                                    $codContribuyente,
                                ]
                            );

                            $apellidoPaterno =
                                $perfil->apellido_paterno;

                            $apellidoMaterno =
                                $perfil->apellido_materno;

                            $nombres =
                                $perfil->nombres;

                            $telefono =
                                $perfil->telefono;

                            $email =
                                $usuario->correo_electronico;

                            $tipoDocumento =
                                $perfil->tipoDocumento;

                            if (! $tipoDocumento) {

                                Log::channel('niubiz')->error(
                                    'PERFIL SIN TIPO DE DOCUMENTO',
                                    [
                                        'perfil_id' => $perfil->id,
                                        'tipo_documento_id' =>
                                        $perfil->tipo_documento_id,
                                        'reserva_id' => $reserva->id,
                                    ]
                                );

                                throw new \Exception(
                                    'El perfil no tiene tipo de documento asociado. tipo_documento_id: '
                                        . ($perfil->tipo_documento_id ?? 'NULL')
                                );
                            }

                            $tipo_doi =
                                $tipoDocumento->doi;

                            $distritoId =
                                $perfil->ubigeo_distrito;

                            Log::channel('niubiz')->info(
                                '[Oracle] Buscando código de distrito',
                                [
                                    'distrito_id' => $distritoId,
                                    'reserva_id' => $reserva->id,
                                ]
                            );

                            $codDistrito =
                                Distrito::where(
                                    'id',
                                    (int) $distritoId
                                )->value('codigo');

                            $direccion =
                                $perfil->direccion;

                            /*
                            |--------------------------------------------------------------------------
                            | LOG DATOS PREVIOS INSERT ORACLE
                            |--------------------------------------------------------------------------
                            */

                            Log::channel('niubiz')->info(
                                '[Oracle] DATOS PREVIOS AL INSERT',
                                [
                                    'cod_contribuyente' =>
                                    $codContribuyente,
                                    'numero_documento' =>
                                    $num_documento,
                                    'apellido_paterno' =>
                                    $apellidoPaterno,
                                    'apellido_materno' =>
                                    $apellidoMaterno,
                                    'nombres' =>
                                    $nombres,
                                    'telefono' =>
                                    $telefono,
                                    'email' =>
                                    $email,
                                    'tipo_doi' =>
                                    $tipo_doi,
                                    'tipo_documento' =>
                                    $tipoDocumento->nombre,
                                    'distrito_id' =>
                                    $distritoId,
                                    'codigo_distrito' =>
                                    $codDistrito,
                                    'direccion' =>
                                    $direccion,
                                ]
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | INSERTAR CONTRIBUYENTE EN ORACLE
                            |--------------------------------------------------------------------------
                            */

                            try {

                                Log::channel('niubiz')->info(
                                    '[Oracle] Ejecutando INSERT en SMACARNOM',
                                    [
                                        'cod_contribuyente' =>
                                        $codContribuyente,
                                        'numero_documento' =>
                                        $num_documento,
                                        'reserva_id' =>
                                        $reserva->id,
                                    ]
                                );

                                $contribuyente =
                                    DB::connection('oracle')
                                    ->insert(
                                        "INSERT INTO SMACARNOM
                                        (
                                            MCNCONTRIB,
                                            MCNESTADO,
                                            MCNTIPO,
                                            MCNAPEPAT,
                                            MCNAPEMAT,
                                            MCNNOMBRE,
                                            MCNVIAS,
                                            MCNDIRE,
                                            MCNNUME,
                                            MCNDPTO,
                                            MCNCODURBA,
                                            MCNURBA,
                                            MCNMANZ,
                                            MCNLOTE,
                                            MCNAPENOMB,
                                            MCNTIPODI,
                                            MCNNRODI,
                                            MCNTIPTELE,
                                            MCNROTELE,
                                            MCNEMAIL,
                                            MCNDNI,
                                            MCNRUC,
                                            DISTRICODI,
                                            MCNFECNAC,
                                            CODCAT,
                                            SEXO
                                        )
                                        VALUES
                                        (
                                            ?,
                                            'ERE04',
                                            'TPE01',
                                            ?,
                                            ?,
                                            ?,
                                            NULL,
                                            ?,
                                            NULL,
                                            NULL,
                                            NULL,
                                            NULL,
                                            NULL,
                                            NULL,
                                            ?,
                                            ?,
                                            ?,
                                            TRIM('02'),
                                            ?,
                                            ?,
                                            ?,
                                            NULL,
                                            ?,
                                            NULL,
                                            NULL,
                                            NULL
                                        )",
                                        [
                                            $codContribuyente,

                                            trim(
                                                (string)
                                                $apellidoPaterno
                                            ),

                                            trim(
                                                (string)
                                                $apellidoMaterno
                                            ),

                                            trim(
                                                (string)
                                                $nombres
                                            ),

                                            trim(
                                                (string)
                                                $direccion
                                            ),

                                            trim(
                                                $apellidoPaterno
                                                    . ' '
                                                    . $apellidoMaterno
                                                    . ' '
                                                    . $nombres
                                            ),

                                            trim(
                                                (string)
                                                $tipo_doi
                                            ),

                                            trim(
                                                (string)
                                                $num_documento
                                            ),

                                            trim(
                                                (string)
                                                $telefono
                                            ),

                                            trim(
                                                (string)
                                                $email
                                            ),

                                            $tipoDocumento->abreviatura
                                                === 'DNI'
                                                ? trim(
                                                    (string)
                                                    $num_documento
                                                )
                                                : null,

                                            trim(
                                                (string)
                                                $codDistrito
                                            ),
                                        ]
                                    );

                                Log::channel('niubiz')->info(
                                    '[Oracle] INSERT CONTRIBUYENTE CORRECTO',
                                    [
                                        'resultado' =>
                                        $contribuyente,
                                        'cod_contribuyente' =>
                                        $codContribuyente,
                                        'numero_documento' =>
                                        $num_documento,
                                        'reserva_id' =>
                                        $reserva->id,
                                    ]
                                );
                            } catch (\Throwable $e) {

                                Log::channel('niubiz')->error(
                                    '[Oracle] ERROR INSERTANDO CONTRIBUYENTE',
                                    [
                                        'mensaje' =>
                                        $e->getMessage(),
                                        'archivo' =>
                                        $e->getFile(),
                                        'linea' =>
                                        $e->getLine(),
                                        'cod_contribuyente' =>
                                        $codContribuyente,
                                        'numero_documento' =>
                                        $num_documento,
                                        'tipo_doi' =>
                                        $tipo_doi,
                                        'codigo_distrito' =>
                                        $codDistrito,
                                        'reserva_id' =>
                                        $reserva->id,
                                    ]
                                );

                                throw $e;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | ACTUALIZAR PERFIL LOCAL
                            |--------------------------------------------------------------------------
                            */

                            $perfil->update([
                                'cod_contrib' =>
                                $codContribuyente,
                            ]);

                            Log::channel('niubiz')->info(
                                '[Oracle] COD_CONTRIB ACTUALIZADO EN PERFIL',
                                [
                                    'perfil_id' =>
                                    $perfil->id,
                                    'cod_contrib' =>
                                    $perfil->cod_contrib,
                                ]
                            );
                        }

                        Log::channel('niubiz')->info(
                            '[Oracle] PROCESO CONTRIBUYENTE FINALIZADO',
                            [
                                'reserva_id' =>
                                $reserva->id,
                                'perfil_id' =>
                                $perfil->id,
                                'cod_contrib' =>
                                $perfil->cod_contrib,
                            ]
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TUSNE
                |--------------------------------------------------------------------------
                */

                $catalogoTusneId =
                    CatalogoTusneReserva::idDesdeMeta($meta);

                Log::channel('niubiz')->info(
                    '[Oracle] Buscando catálogo TUSNE',
                    [
                        'catalogo_tusne_id' => $catalogoTusneId,
                        'reserva_id' => $reserva->id,
                    ]
                );

                $tusne = CatalogoTusne::find(
                    $catalogoTusneId
                );

                if (! $tusne) {

                    Log::channel('niubiz')->error(
                        '[Oracle] CATALOGO TUSNE NO ENCONTRADO',
                        [
                            'catalogo_tusne_id' => $catalogoTusneId,
                            'reserva_id' => $reserva->id,
                        ]
                    );

                    throw new \Exception(
                        'No se encontró el catálogo TUSNE asociado al pago.'
                    );
                }

                Log::channel('niubiz')->info(
                    '[Oracle] Catálogo TUSNE encontrado',
                    [
                        'catalogo_tusne_id' => $tusne->id,
                        'grupo_tusne' => $tusne->grupo_tusne,
                        'codigo_tusne' => $tusne->codigo_tusne,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | GENERAR NUMERO DE LIQUIDACION
                |--------------------------------------------------------------------------
                */

                Log::channel('niubiz')->info(
                    '[Oracle] Generando número de liquidación',
                    [
                        'grupo_tusne' => $tusne->grupo_tusne,
                        'codigo_tusne' => $tusne->codigo_tusne,
                        'cod_contribuyente' => $codContribuyente,
                        'reserva_id' => $reserva->id,
                    ]
                );

                $resNumLiquidacion =
                    $serviceOracle->generarNumLiquidacion(
                        $tusne->grupo_tusne,
                        $tusne->codigo_tusne,
                        $codContribuyente
                    );

                $numLiquidacion = null;

                if (! empty($resNumLiquidacion)) {

                    $numLiquidacion =
                        $resNumLiquidacion[0]->liquidacion;

                    Log::channel('niubiz')->info(
                        '[Oracle] Número de liquidación generado',
                        [
                            'num_liquidacion' => $numLiquidacion,
                            'reserva_id' => $reserva->id,
                        ]
                    );
                } else {

                    Log::channel('niubiz')->warning(
                        '[Oracle] No se recibió número de liquidación',
                        [
                            'reserva_id' => $reserva->id,
                            'cod_contribuyente' => $codContribuyente,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR CANTIDAD DE CONCEPTOS TUSNES
                |--------------------------------------------------------------------------
                */

                $cantidadHoras = $reserva->cantidad_horas;

                Log::channel('niubiz')->info(
                    '[Oracle] Actualizando cantidad de conceptos TUSNE',
                    [
                        'reserva_id' => $reserva->id,
                        'cantidad_horas' => $cantidadHoras,
                        'num_liquidacion' => $numLiquidacion,
                    ]
                );

                $serviceOracle->actualizarCantidadConcepto(
                    $cantidadHoras,
                    $numLiquidacion
                );

                Log::channel('niubiz')->info(
                    '[Oracle] Cantidad de conceptos TUSNE actualizada',
                    [
                        'reserva_id' => $reserva->id,
                        'cantidad_horas' => $cantidadHoras,
                        'num_liquidacion' => $numLiquidacion,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR PAGO CON LIQUIDACION
                |--------------------------------------------------------------------------
                */

                $pago->update([
                    'num_liquidacion' => $numLiquidacion ?? null
                ]);

                Log::channel('niubiz')->info(
                    '[Verify] Pago actualizado con número de liquidación',
                    [
                        'pago_id' => $pago->id,
                        'num_liquidacion' => $numLiquidacion,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | INSERTAR PAGO EN ORACLE
                |--------------------------------------------------------------------------
                */

                Log::channel('niubiz')->info(
                    '[Oracle] Insertando pago/concepto en Oracle',
                    [
                        'grupo_tusne' =>
                        $tusne->grupo_tusne,
                        'codigo_tusne' =>
                        $tusne->codigo_tusne,
                        'cod_contribuyente' =>
                        $codContribuyente,
                        'monto' =>
                        $pago->monto,
                        'purchase_number' =>
                        $purchaseNumber,
                        'transaccion_id' =>
                        $pago->transaccion_id,
                        'pagado_en' =>
                        $pago->pagado_en,
                        'num_liquidacion' =>
                        $numLiquidacion,
                        'cantidad_horas' =>
                        $cantidadHoras,
                        'reserva_id' =>
                        $reserva->id,
                    ]
                );

                $serviceOracle->insertarEnOracle(
                    $tusne->grupo_tusne,
                    $tusne->codigo_tusne,
                    $codContribuyente,
                    $pago->monto,
                    (int) $reserva->id,
                    $pago->transaccion_id,
                    $pago->pagado_en,
                    $numLiquidacion,
                    $cantidadHoras
                );

                Log::channel('niubiz')->info(
                    '[Oracle] INSERTAR PAGO EN ORACLE COMPLETADO',
                    [
                        'reserva_id' => $reserva->id,
                        'pago_id' => $pago->id,
                        'num_liquidacion' => $numLiquidacion,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | ENVIAR CORREO
                |--------------------------------------------------------------------------
                */

                Log::channel('niubiz')->info(
                    '[Correo] Iniciando envío de confirmación de pago',
                    [
                        'reserva_id' => $reserva->id,
                        'pago_id' => $pago->id,
                        'usuario_nuevo' => $usuarioNuevo,
                    ]
                );

                app(ReservaCorreoService::class)
                    ->enviarConfirmacionPago(
                        $reserva,
                        $meta,
                        $usuarioNuevo,
                        is_string($usuarioLogin)
                            ? $usuarioLogin
                            : null,
                        is_string($clavePlana)
                            ? $clavePlana
                            : null,
                        $pagoRegistrado,
                    );

                Log::channel('niubiz')->info(
                    '[Correo] Confirmación de pago enviada correctamente',
                    [
                        'reserva_id' => $reserva->id,
                        'pago_id' => $pago->id,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | LIMPIAR SESSION
                |--------------------------------------------------------------------------
                */

                Log::channel('niubiz')->info(
                    '[Verify] Limpiando variables de sesión del proceso de pago',
                    [
                        'reserva_id' => $reserva->id,
                    ]
                );

                session()->forget([
                    'pago_reserva_id',
                    'pago_acepto_terminos',
                    'pago_usuario_nuevo',
                    'pago_usuario_login',
                    'pago_clave_plana',
                ]);

                Log::channel('niubiz')->info(
                    "[Verify] VARIABLES DE SESION LIMPIADAS"
                );

                Log::channel('niubiz')->info(
                    "[Verify] ================================================"
                );

                Log::channel('niubiz')->info(
                    "[Verify] PAGO AUTORIZADO Y PROCESADO CORRECTAMENTE",
                    [
                        'reserva_id' => $reserva->id,
                        'purchase_number' => $purchaseNumber,
                        'pago_id' => $pago->id,
                        'num_liquidacion' => $numLiquidacion,
                        'cod_contribuyente' => $codContribuyente,
                    ]
                );

                Log::channel('niubiz')->info(
                    "[Verify] FIN PROCESO EXITOSO"
                );

                return $this->redirectResultado(
                    'exitoso',
                    $reserva
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PAGO DENEGADO
            |--------------------------------------------------------------------------
            */

            Log::channel('niubiz')->warning(
                '[Verify] PAGO NO AUTORIZADO POR NIUBIZ',
                [
                    'contexto_id' => $reserva?->id ?? $purchaseNumber,
                    'purchase_number' => $purchaseNumber,
                    'action_code' => $actionCode,
                    'status' => $isAuthorized,
                    'transaction_id' => $transactionId,
                    'es_checkout' => $esCheckout,
                ]
            );

            $friendly =
                data_get(
                    $response,
                    'dataMap.ACTION_DESCRIPTION'
                )
                ?? data_get(
                    $response,
                    'data.ACTION_DESCRIPTION'
                )
                ?? 'El pago fue denegado.';

            if ($esCheckout) {
                $checkoutService->liberar($purchaseNumber);

                Log::channel('niubiz')->warning(
                    "[Verify] Pago denegado en checkout #{$purchaseNumber}",
                    [
                        'action_code' => $actionCode,
                        'mensaje' => $friendly,
                        'status' => $isAuthorized,
                    ]
                );

                return $this->redirectResultadoSinReserva(
                    'denegado',
                    $friendly,
                    $checkout,
                );
            }

            Transaccion::create([
                'reserva_id' => $reserva->id,

                'transaccion_id' =>
                (string) $transactionId,

                'codigo_autorizacion' =>
                $authCode
                    ? (string) $authCode
                    : null,

                'marca_tarjeta' =>
                $brand
                    ? (string) $brand
                    : null,

                'tarjeta_enmascarada' =>
                $card
                    ? (string) $card
                    : null,

                'monto' => round(
                    (float) $amount,
                    2
                ),

                'estado' => 'Denied',

                'respuesta_bruta' => [
                    'niubiz' => $response,
                    'action_code' => $actionCode,
                ],
            ]);

            Log::channel('niubiz')->info(
                '[Verify] Transacción denegada registrada localmente',
                [
                    'reserva_id' => $reserva->id,
                    'transaction_id' => $transactionId,
                    'action_code' => $actionCode,
                    'monto' => $amount,
                ]
            );

            $reserva->update([
                'estado' => 'pago_fallido'
            ]);

            Log::channel('niubiz')->warning(
                '[Verify] Reserva marcada como pago_fallido',
                [
                    'reserva_id' => $reserva->id,
                    'purchase_number' => $purchaseNumber,
                ]
            );

            Log::channel('niubiz')->warning(
                "[Verify] Pago denegado #{$reserva->id}",
                [
                    'action_code' => $actionCode,
                    'mensaje' => $friendly,
                ]
            );

            return $this->redirectResultado(
                'denegado',
                $reserva,
                $friendly
            );
        } catch (\Throwable $e) {

            Log::channel('niubiz')->error(
                '[Verify] ERROR NO CONTROLADO DURANTE LA VERIFICACION DEL PAGO',
                [
                    'reserva_id' => $reserva->id ?? null,
                    'purchase_number' => $purchaseNumber,
                    'mensaje' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        } finally {

            Log::channel('niubiz')->info(
                '[Verify] Liberando lock de procesamiento',
                [
                    'reserva_id' => $reserva->id ?? null,
                    'purchase_number' => $purchaseNumber,
                ]
            );

            optional($lock)->release();

            Log::channel('niubiz')->info(
                '[Verify] Lock liberado',
                [
                    'reserva_id' => $reserva->id ?? null,
                    'purchase_number' => $purchaseNumber,
                ]
            );
        }
    }

    public function generarCodigoContribuyente()
    {
        Log::channel('niubiz')->info(
            '[Oracle] Generando código de contribuyente'
        );

        $resultado = DB::connection('oracle')->selectOne(
            "SELECT 'S' || LPAD(ADMIN.SEQ_MACARNOM.NEXTVAL, 7, '0') MCNCONTRIB FROM DUAL"
        );

        if (! $resultado || empty($resultado->mcncontrib)) {

            Log::channel('niubiz')->error(
                '[Oracle] NO SE PUDO GENERAR CODIGO DE CONTRIBUYENTE'
            );

            throw new \Exception(
                'No se pudo generar el código de contribuyente en Oracle.'
            );
        }

        $codigo = $resultado->mcncontrib;

        Log::channel('niubiz')->info(
            '[Oracle] Código de contribuyente generado',
            [
                'cod_contribuyente' => $codigo,
            ]
        );

        return $codigo;
    }

    private function redirectResultadoSinReserva(
        string $estado,
        ?string $mensaje = null,
        ?array $checkout = null,
    ): RedirectResponse {
        if ($mensaje) {
            session()->flash('pago_resultado_mensaje', $mensaje);
        }

        if (is_array($checkout)) {
            session()->flash('pago_resultado_comprobante', [
                'numero_pedido' => (string) ($checkout['voucher'] ?? $checkout['purchase_number'] ?? '—'),
                'fecha_pedido_label' => now('America/Lima')->format('d/m/Y H:i:s'),
                'descripcion_denegacion' => $mensaje ?? '',
                'importe_label' => isset($checkout['precio'])
                    ? 'S/ ' . number_format((float) $checkout['precio'], 2)
                    : '—',
            ]);

            if (! empty($checkout['meta']) && is_array($checkout['meta'])) {
                session()->flash('pago_meta', $checkout['meta']);
            }

            if (! empty($checkout['return_query'])) {
                session()->flash('pago_return_query', $checkout['return_query']);
            }
        }

        return redirect(
            ReservaFlow::rutaResultado($estado, ['estado' => $estado])
        );
    }

    private function redirectResultado(
        string $estado,
        Reserva $reserva,
        ?string $mensaje = null
    ): RedirectResponse {

        Log::channel('niubiz')->info(
            '[Verify] Preparando redirección de resultado',
            [
                'reserva_id' => $reserva->id,
                'estado' => $estado,
                'tiene_mensaje' => ! empty($mensaje),
            ]
        );

        $params = [
            'estado' => $estado,
            'reserva' => $reserva->id,
        ];

        if ($estado === 'exitoso') {

            $params['voucher'] =
                $reserva->referencia_pago;
        }

        if ($mensaje) {

            session()->flash(
                'pago_resultado_mensaje',
                $mensaje
            );
        }

        return redirect(
            ReservaFlow::rutaResultado(
                $estado,
                $params
            )
        );
    }
}
