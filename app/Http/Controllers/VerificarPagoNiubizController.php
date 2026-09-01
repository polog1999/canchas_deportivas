<?php

namespace App\Http\Controllers;

use App\Models\CatalogoTusne;
use App\Models\Distrito;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Transaccion;
use App\Services\NiubizService;
use App\Services\OracleService;
use App\Services\ReservaCorreoService;
use App\Support\CatalogoTusneReserva;
use App\Support\ReservaFlow;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            "[Verify] Callback recibido compra #{$purchaseNumber}",
            [
                'keys' => array_keys($request->all()),
            ]
        );

        $reserva = Reserva::query()->find($purchaseNumber);

        if (! $reserva) {

            Log::channel('niubiz')->error(
                "[Verify] Reserva #{$purchaseNumber} no encontrada"
            );

            return redirect('/')->with(
                'error',
                'Reserva de pago no encontrada.'
            );
        }

        if (strtolower((string) $reserva->estado) === 'confirmada') {

            Log::channel('niubiz')->warning(
                "[Verify] Reserva #{$purchaseNumber} ya confirmada"
            );

            return $this->redirectResultado(
                'exitoso',
                $reserva
            );
        }

        $lock = Cache::lock(
            'verify_payment_reserva_' . $purchaseNumber,
            15
        );

        if (! $lock->get()) {

            for ($i = 0; $i < 6; $i++) {

                usleep(500000);

                $reserva->refresh();

                if (
                    strtolower((string) $reserva->estado)
                    === 'confirmada'
                ) {

                    return $this->redirectResultado(
                        'exitoso',
                        $reserva
                    );
                }
            }

            return $this->redirectResultado(
                'procesando',
                $reserva,
                'El pago ya se está procesando. Espera un momento y revisa el resultado.'
            );
        }

        try {

            $transactionToken = $request->input(
                'transactionToken'
            );

            if (! $transactionToken) {

                Log::channel('niubiz')->error(
                    '[Verify] Falta transactionToken'
                );

                return $this->redirectResultado(
                    'error',
                    $reserva,
                    'Respuesta de pago inválida.'
                );
            }

            $monto = round(
                (float) $reserva->precio_total,
                2
            );

            /*
        |--------------------------------------------------------------------------
        | AUTORIZAR PAGO EN NIUBIZ
        |--------------------------------------------------------------------------
        */

            $response = $niubiz->authorizeTransaction(
                $transactionToken,
                $reserva,
                $monto
            );

            if ($response === null) {

                $reserva->update([
                    'estado' => 'pago_fallido'
                ]);

                return $this->redirectResultado(
                    'error',
                    $reserva,
                    'No se pudo autorizar el pago. Intenta nuevamente.'
                );
            }

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

            /*
        |--------------------------------------------------------------------------
        | PAGO AUTORIZADO
        |--------------------------------------------------------------------------
        */

            if ($isAuthorized === 'Authorized') {

                Log::channel('niubiz')->info(
                    "[Verify] Niubiz autorizó el pago",
                    [
                        'reserva_id' => $reserva->id,
                        'purchase_number' => $purchaseNumber,
                        'transaction_id' => $transactionId,
                        'authorization_code' => $authCode,
                        'amount' => $amount,
                    ]
                );

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

                /*
            |--------------------------------------------------------------------------
            | GUARDAR RESERVA + TRANSACCION + PAGO
            |--------------------------------------------------------------------------
            */

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

                        $reserva->refresh();

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

                            return Pago::whereHas(
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
                        }

                        $reserva->update([
                            'estado' => 'confirmada'
                        ]);

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

                        Log::channel('niubiz')->info(
                            '[Verify] Pago creado correctamente',
                            [
                                'pago_id' => $pago->id,
                                'transaccion_id' => $transaccion->id,
                                'reserva_id' => $reserva->id,
                                'id_catalogos_tusne' =>
                                $pago->id_catalogos_tusne,
                                'monto' => $pago->monto,
                            ]
                        );

                        return $pago;
                    }
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
                    ]
                );

                /*
            |--------------------------------------------------------------------------
            | OBTENER USUARIO Y PERFIL
            |--------------------------------------------------------------------------
            |
            | Se utiliza el usuario autenticado que realizó la reserva.
            |
            */
                $serviceOracle = app(OracleService::class);
                $usuario = auth()->user();

                if (! $usuario) {

                    Log::channel('niubiz')->error(
                        '[Oracle] No se pudo obtener el usuario autenticado',
                        [
                            'reserva_id' => $reserva->id,
                        ]
                    );
                } else {

                    $perfil = $usuario->perfil;

                    if (! $perfil) {

                        Log::channel('niubiz')->error(
                            '[Oracle] El usuario no tiene perfil asociado',
                            [
                                'usuario_id' => $usuario->id,
                                'reserva_id' => $reserva->id,
                            ]
                        );
                    } else {

                        $num_documento =
                            trim(
                                (string) $perfil->numero_documento
                            );

                        Log::channel('niubiz')->info(
                            '[Oracle] Datos del usuario obtenidos',
                            [
                                'usuario_id' => $usuario->id,
                                'perfil_id' => $perfil->id,
                                'numero_documento' =>
                                $num_documento,
                                'cod_contrib_actual' =>
                                $perfil->cod_contrib,
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
                                    'numero_documento' =>
                                    $num_documento,
                                    'reserva_id' =>
                                    $reserva->id,
                                ]
                            );

                            $codContribuyente =
                                $serviceOracle->getCodContribuyente(
                                    $num_documento
                                );

                            Log::channel('niubiz')->info(
                                '[Oracle] Resultado consulta contribuyente',
                                [
                                    'numero_documento' =>
                                    $num_documento,
                                    'cod_contribuyente' =>
                                    $codContribuyente,
                                ]
                            );
                        } catch (\Throwable $e) {

                            Log::channel('niubiz')->error(
                                '[Oracle] ERROR CONSULTANDO CONTRIBUYENTE',
                                [
                                    'numero_documento' =>
                                    $num_documento,
                                    'mensaje' =>
                                    $e->getMessage(),
                                    'archivo' =>
                                    $e->getFile(),
                                    'linea' =>
                                    $e->getLine(),
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
                                    'numero_documento' =>
                                    $num_documento,
                                    'cod_contribuyente' =>
                                    $codContribuyente,
                                ]
                            );

                            /*
                        | Guardamos el código Oracle
                        | también en nuestro perfil local.
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

                        /*
                    |--------------------------------------------------------------------------
                    | NO EXISTE -> CREAR CONTRIBUYENTE
                    |--------------------------------------------------------------------------
                    */ else {

                            Log::channel('niubiz')->info(
                                '[Oracle] CONTRIBUYENTE NO EXISTE - CREANDO',
                                [
                                    'numero_documento' =>
                                    $num_documento,
                                ]
                            );

                            /*
                        | Generar código usando
                        | SEQ_MACARNOM.NEXTVAL
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

                            $tipoDocumento = $perfil->tipoDocumento;

                            if (!$tipoDocumento) {
                                Log::error('PERFIL SIN TIPO DE DOCUMENTO', [
                                    'perfil_id' => $perfil->id,
                                    'tipo_documento_id' => $perfil->tipo_documento_id,
                                ]);

                                throw new \Exception(
                                    'El perfil no tiene tipo de documento asociado. tipo_documento_id: '
                                        . ($perfil->tipo_documento_id ?? 'NULL')
                                );
                            }

                            $tipo_doi = $tipoDocumento->doi;

                            $distritoId =
                                $perfil->ubigeo_distrito;

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
                $tusne = CatalogoTusne::find(CatalogoTusneReserva::idDesdeMeta($meta));

                $resNumLiquidacion = $serviceOracle->generarNumLiquidacion($tusne->grupo_tusne, $tusne->codigo_tusne, $codContribuyente);
                $numLiquidacion = null;

                if (!empty($resNumLiquidacion)) {
                    $numLiquidacion =  $resNumLiquidacion[0]->liquidacion;
                }

                //================================================ACTUALIZANDO LA CANTIDAD DE CONCEPTOS TUSNES CON LA CANTIDAD DE HORAS ========================================
                // $horaInicio = Carbon::parse($reserva->hora_inicio);
                // $horaFin    = Carbon::parse($reserva->hora_fin);

                // $cantidadHoras = (int) $horaInicio->diffInHours($horaFin);
                $cantidadHoras = $reserva->cantidad_horas;
                $serviceOracle->actualizarCantidadConcepto($cantidadHoras, $numLiquidacion);


                $pago->update([
                    'num_liquidacion' => $numLiquidacion ?? null
                ]);

                $serviceOracle->insertarEnOracle($tusne->grupo_tusne, $tusne->codigo_tusne, $codContribuyente, $pago->monto, $purchaseNumber, $pago->transaccion_id, $pago->pagado_en, $numLiquidacion, $cantidadHoras);




                /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO
            |--------------------------------------------------------------------------
            */

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

                /*
            |--------------------------------------------------------------------------
            | LIMPIAR SESSION
            |--------------------------------------------------------------------------
            */

                session()->forget([
                    'pago_reserva_id',
                    'pago_acepto_terminos',
                    'pago_usuario_nuevo',
                    'pago_usuario_login',
                    'pago_clave_plana',
                ]);

                Log::channel('niubiz')->info(
                    "[Verify] Pago autorizado reserva #{$reserva->id}"
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

            Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => (string) $transactionId,
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

            $reserva->update([
                'estado' => 'pago_fallido'
            ]);

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

            Log::channel('niubiz')->warning(
                "[Verify] Pago denegado #{$reserva->id}",
                [
                    'action_code' => $actionCode,
                ]
            );

            return $this->redirectResultado(
                'denegado',
                $reserva,
                $friendly
            );
        } finally {

            optional($lock)->release();
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

        $codigo = $resultado->mcncontrib;

        Log::channel('niubiz')->info(
            '[Oracle] Código de contribuyente generado',
            [
                'cod_contribuyente' => $codigo,
            ]
        );

        return $codigo;
    }
    private function redirectResultado(string $estado, Reserva $reserva, ?string $mensaje = null): RedirectResponse
    {
        $params = [
            'estado' => $estado,
            'reserva' => $reserva->id,
        ];

        if ($estado === 'exitoso') {
            $params['voucher'] = $reserva->referencia_pago;
        }

        if ($mensaje) {
            session()->flash('pago_resultado_mensaje', $mensaje);
        }

        return redirect(ReservaFlow::rutaResultado($estado, $params));
    }
}
