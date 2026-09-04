<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\CatalogoTusne;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Transaccion;
use App\Models\Usuario;
use App\Services\NiubizService;
use App\Services\ReservaCheckoutService;
use App\Services\ReservaCorreoService;
use App\Services\ReservaTitularService;
use App\Services\TarifaTusneService;
use App\Support\CatalogoTusneReserva;
use App\Support\ReservaFlow;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarReservaController extends Controller
{
    public function __invoke(
        Request $request,
        NiubizService $niubiz,
        ReservaTitularService $titularService,
        ReservaCheckoutService $checkoutService,
        TarifaTusneService $tarifas,
    ): JsonResponse {
        $data = $request->validate([
            'acepto_terminos' => 'accepted',
            'cancha_id' => 'required|integer|exists:canchas,id',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|string',
            'duracion' => 'required|integer|in:60,120',
            'precio' => 'nullable|numeric|min:0',
            'documento' => 'nullable|string|min:8|max:15',
            'nombres' => 'nullable|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:9',
            'tipo_documento_id' => 'nullable|integer|in:1,2,3',
            'email' => 'nullable|email|max:150',
            'distrito_id' => 'nullable|string|max:20',
            'estado_titular' => 'nullable|in:existe,nuevo',
            'sede' => 'nullable',
            'club' => 'nullable|string|max:255',
            'cancha' => 'nullable|string|max:255',
            'deporte' => 'nullable|string|max:255',
            'deporte_id' => 'nullable',
            'tusne_id' => 'nullable|integer|exists:catalogos_tusne,id',
        ], [
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones.',
            'cancha_id.required' => 'Falta la cancha de la reserva.',
            'fecha.required' => 'Falta la fecha de la reserva.',
            'hora.required' => 'Falta la hora de la reserva.',
        ]);

        $titular = $titularService->prepararParaCheckout($data);

        $hora = preg_replace('/[^\d:]/', '', (string) $data['hora']);
        if (! preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            throw ValidationException::withMessages(['hora' => 'Hora inválida.']);
        }

        $horaInicio = Carbon::createFromFormat('Y-m-d H:i', $data['fecha'].' '.$hora, 'America/Lima');
        $horaFin = $horaInicio->copy()->addMinutes((int) $data['duracion']);

        $cancha = Cancha::query()->whereKey($data['cancha_id'])->where('esta_activo', true)->first();
        if (! $cancha) {
            throw ValidationException::withMessages(['cancha_id' => 'La cancha no está disponible.']);
        }

        $data['tusne_id'] = CatalogoTusneReserva::idDesdeDatos($data, $cancha);

        // El monto se recalcula en el servidor: el que llega del navegador solo se contrasta.
        $precio = $this->precioValidado($tarifas, $cancha, $data, $horaInicio, $horaFin);

        $returnQuery = $request->header('X-Pago-Query') ?: $request->getQueryString();

        if ($precio <= 0) {
            return $this->confirmarSinPasarela($data, $titular, $horaInicio, $horaFin, $precio, $cancha);
        }

        $checkout = $checkoutService->crear(
            $data,
            $titular,
            $horaInicio,
            $horaFin,
            $precio,
            $cancha->id,
            $returnQuery,
        );

        if ($titular['usuario'] && ! Auth::check()) {
            Auth::login($titular['usuario']);
        }

        $sessionKey = $niubiz->createSessionToken(
            $checkout['purchase_number'],
            $precio,
            $checkoutService->datosAntifraude($checkout, $titular['usuario']),
        );

        if (! $sessionKey) {
            $checkoutService->liberar($checkout['purchase_number']);

            throw ValidationException::withMessages([
                'niubiz' => 'No se pudo conectar con la pasarela de pago. Intenta nuevamente.',
            ]);
        }

        $timeoutUrl = ReservaFlow::desdePortalActivo()
            ? route('portal.reservar.pago')
            : route('reservar.pago');
        if ($returnQuery) {
            $timeoutUrl .= '?'.ltrim($returnQuery, '?');
        }

        return response()->json([
            'ok' => true,
            'sin_pasarela' => false,
            'sessionKey' => $sessionKey,
            'purchaseNumber' => $checkout['purchase_number'],
            'amount' => number_format($precio, 2, '.', ''),
            'merchantId' => config('niubiz.merchant_id'),
            'voucher' => $checkout['voucher'],
            'verifyUrl' => route('reservar.pago.verificar', ['purchaseNumber' => $checkout['purchase_number']]),
            'timeoutUrl' => $timeoutUrl,
            'buttonUrl' => config('niubiz.button_url'),
        ]);
    }

    /**
     * Monto real del turno según la tarifa vigente.
     *
     * El navegador manda el precio que mostró en pantalla, pero no se usa para
     * cobrar: sirve solo para detectar que lo que vio el usuario sigue siendo
     * lo que corresponde.
     *
     * @param  array<string, mixed>  $data
     */
    private function precioValidado(
        TarifaTusneService $tarifas,
        Cancha $cancha,
        array $data,
        Carbon $horaInicio,
        Carbon $horaFin,
    ): float {
        $tusne = $data['tusne_id'] ? CatalogoTusne::find($data['tusne_id']) : null;

        if ($tusne && ! $tarifas->tusneCubreRango($tusne, $horaInicio, $horaFin)) {
            throw ValidationException::withMessages([
                'hora' => 'La tarifa elegida no corresponde al turno seleccionado.',
            ]);
        }

        // Si la cancha tiene tarifa para ese turno, no se puede reservar sin ella.
        if (! $tusne && $this->canchaTieneTarifa($tarifas, $cancha, $horaInicio, $horaFin)) {
            throw ValidationException::withMessages([
                'tusne_id' => 'Falta la tarifa del turno. Vuelve a cargar la página e intenta de nuevo.',
            ]);
        }

        $precioServidor = $tarifas->precioTotal($cancha, $tusne, ((int) $data['duracion']) / 60);
        $precioCliente = round((float) ($data['precio'] ?? 0), 2);

        if (abs($precioServidor - $precioCliente) > 0.01) {
            throw ValidationException::withMessages([
                'precio' => 'El precio de este turno es S/ '.number_format($precioServidor, 2)
                    .'. Vuelve a cargar la página para continuar con el monto correcto.',
            ]);
        }

        return $precioServidor;
    }

    private function canchaTieneTarifa(
        TarifaTusneService $tarifas,
        Cancha $cancha,
        Carbon $horaInicio,
        Carbon $horaFin,
    ): bool {
        return $cancha->catalogosTusne()
            ->where('esta_activo', true)
            ->get()
            ->contains(fn (CatalogoTusne $t) => $tarifas->tusneCubreRango($t, $horaInicio, $horaFin));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{
     *     usuario: Usuario|null,
     *     es_nuevo: bool,
     *     datos_registro: array<string, mixed>|null,
     *     usuario_login: string|null,
     *     clave_plana: string|null
     * }  $titular
     */
    private function confirmarSinPasarela(
        array $data,
        array $titular,
        Carbon $horaInicio,
        Carbon $horaFin,
        float $precio,
        Cancha $cancha,
    ): JsonResponse {
        $usuario = $titular['usuario'];

        if (! $usuario && is_array($titular['datos_registro'])) {
            $usuario = app(ReservaTitularService::class)->crearUsuario($titular['datos_registro']);
            $titular['es_nuevo'] = true;
        }

        if (! $usuario) {
            throw ValidationException::withMessages([
                'documento' => 'No se pudo identificar al titular de la reserva.',
            ]);
        }

        if (! Auth::check()) {
            Auth::login($usuario);
        }

        $resultado = DB::transaction(function () use ($data, $usuario, $cancha, $horaInicio, $horaFin, $precio) {
            if (! app(ReservaCheckoutService::class)->turnoDisponible($cancha->id, $horaInicio, $horaFin)) {
                throw ValidationException::withMessages([
                    'hora' => 'Ese horario ya no está disponible. Elige otro turno.',
                ]);
            }

            $codigoVoucher = 'VCH-'.strtoupper(Str::random(8));
            $catalogoTusneId = CatalogoTusneReserva::idDesdeDatos($data, $cancha);

            $reserva = Reserva::create([
                'usuario_id' => $usuario->id,
                'cancha_id' => $cancha->id,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'precio_total' => $precio,
                'referencia_pago' => $codigoVoucher,
                'estado' => 'confirmada',
                'cantidad_horas' => (int) ((int) $data['duracion'] / 60),
            ]);

            $transaccion = Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => 'LOCAL-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'codigo_autorizacion' => null,
                'marca_tarjeta' => null,
                'tarjeta_enmascarada' => null,
                'monto' => 0,
                'estado' => 'SIN_PASARELA',
                'respuesta_bruta' => [
                    'origen' => 'monto_cero',
                    'sin_niubiz' => true,
                    'voucher' => $reserva->referencia_pago,
                    'titular' => [
                        'documento' => $data['documento'] ?? null,
                        'email' => $data['email'] ?? $usuario->correo_electronico,
                    ],
                    'reserva' => [
                        'sede_id' => $data['sede'] ?? $cancha->sede_id,
                        'club' => $data['club'] ?? null,
                        'cancha' => $data['cancha'] ?? $cancha->nombre,
                        'deporte' => $data['deporte'] ?? null,
                    ],
                ],
            ]);

            $pago = Pago::create([
                'transaccion_id' => $transaccion->id,
                'monto' => 0,
                'pagado_en' => now(),
                'acepto_terminos' => true,
                'id_catalogos_tusne' => $catalogoTusneId,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'pago_id' => $pago->id,
                'voucher' => $reserva->referencia_pago,
                'reserva' => $reserva,
            ];
        });

        app(ReservaCorreoService::class)->enviarConfirmacionPago(
            $resultado['reserva'],
            array_merge($data, [
                'club' => $data['club'] ?? null,
                'cancha' => $data['cancha'] ?? $cancha->nombre,
                'deporte' => $data['deporte'] ?? null,
                'email' => $data['email'] ?? $usuario->correo_electronico,
            ]),
            $titular['es_nuevo'],
            $titular['usuario_login'],
            $titular['clave_plana'],
            Pago::query()->find($resultado['pago_id']),
        );

        return response()->json([
            'ok' => true,
            'sin_pasarela' => true,
            'mensaje' => 'Reserva confirmada (sin costo).',
            'reserva_id' => $resultado['reserva_id'],
            'voucher' => $resultado['voucher'],
            'redirect' => ReservaFlow::rutaResultado('exitoso', [
                'reserva' => $resultado['reserva_id'],
                'voucher' => $resultado['voucher'],
            ]),
        ]);
    }
}
