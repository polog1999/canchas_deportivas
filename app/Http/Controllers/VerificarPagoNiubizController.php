<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Transaccion;
use App\Services\NiubizService;
use App\Services\ReservaCorreoService;
use App\Support\ReservaFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarPagoNiubizController extends Controller
{
    public function __invoke(Request $request, string $purchaseNumber, NiubizService $niubiz): RedirectResponse
    {
        Log::channel('niubiz')->info("[Verify] Callback recibido compra #{$purchaseNumber}", [
            'keys' => array_keys($request->all()),
        ]);

        $pagoUrl = function (?string $error = null) {
            $url = ReservaFlow::desdePortalActivo()
                ? route('portal.reservar.pago')
                : route('reservar.pago');
            $qs = session('pago_return_query');
            if ($qs) {
                $url .= '?'.ltrim($qs, '?');
            }
            $redirect = redirect($url);
            if ($error) {
                $redirect->with('error', $error);
            }

            return $redirect;
        };

        $reserva = Reserva::query()->find($purchaseNumber);

        if (! $reserva) {
            Log::channel('niubiz')->error("[Verify] Reserva #{$purchaseNumber} no encontrada");

            return redirect('/')->with('error', 'Reserva de pago no encontrada.');
        }

        if (strtolower((string) $reserva->estado) === 'confirmada') {
            Log::channel('niubiz')->warning("[Verify] Reserva #{$purchaseNumber} ya confirmada");

            if (ReservaFlow::desdePortalActivo()) {
                ReservaFlow::limpiarMarcaPortal();

                return redirect()
                    ->route('mis-pagos.index')
                    ->with('success', '¡Pago completado! Tu reserva quedó confirmada.');
            }

            return redirect('/?reserva='.$reserva->id.'&pago=ok')
                ->with('success', '¡Pago completado con éxito!');
        }

        $lock = Cache::lock('verify_payment_reserva_'.$purchaseNumber, 15);

        if (! $lock->get()) {
            for ($i = 0; $i < 6; $i++) {
                usleep(500000);
                $reserva->refresh();
                if (strtolower((string) $reserva->estado) === 'confirmada') {
                    if (ReservaFlow::desdePortalActivo()) {
                        ReservaFlow::limpiarMarcaPortal();

                        return redirect()
                            ->route('mis-pagos.index')
                            ->with('success', '¡Pago completado! Tu reserva quedó confirmada.');
                    }

                    return redirect('/?reserva='.$reserva->id.'&pago=ok')
                        ->with('success', '¡Pago completado con éxito!');
                }
            }

            return $pagoUrl('El pago ya se está procesando. Espera un momento.');
        }

        try {
            $transactionToken = $request->input('transactionToken');

            if (! $transactionToken) {
                Log::channel('niubiz')->error('[Verify] Falta transactionToken');

                return $pagoUrl('Respuesta de pago inválida.');
            }

            $monto = round((float) $reserva->precio_total, 2);
            $response = $niubiz->authorizeTransaction($transactionToken, $reserva, $monto);

            if ($response === null) {
                $reserva->update(['estado' => 'pago_fallido']);

                return $pagoUrl('No se pudo autorizar el pago. Intenta nuevamente.');
            }

            $isAuthorized = data_get($response, 'dataMap.STATUS');
            $actionCode = data_get($response, 'dataMap.ACTION_CODE')
                ?? data_get($response, 'data.ACTION_CODE')
                ?? data_get($response, 'ACTION_CODE');

            $transactionId = data_get($response, 'order.transactionId')
                ?? data_get($response, 'dataMap.TRANSACTION_ID')
                ?? ('NIUBIZ-'.$purchaseNumber.'-'.now()->format('His'));

            $authCode = data_get($response, 'order.authorizationCode')
                ?? data_get($response, 'dataMap.AUTHORIZATION_CODE');

            $brand = data_get($response, 'dataMap.BRAND') ?? data_get($response, 'data.BRAND');
            $card = data_get($response, 'dataMap.CARD') ?? data_get($response, 'data.CARD');
            $amount = data_get($response, 'order.amount')
                ?? data_get($response, 'dataMap.AMOUNT')
                ?? $monto;

            if ($isAuthorized === 'Authorized') {
                $meta = session('pago_meta', []);
                $usuarioNuevo = (bool) session('pago_usuario_nuevo', false);
                $usuarioLogin = session('pago_usuario_login');
                $clavePlana = session('pago_clave_plana');

                DB::transaction(function () use ($reserva, $response, $transactionId, $authCode, $brand, $card, $amount, $meta) {
                    $reserva->refresh();
                    if (strtolower((string) $reserva->estado) === 'confirmada') {
                        return;
                    }

                    $reserva->update(['estado' => 'confirmada']);

                    $transaccion = Transaccion::create([
                        'reserva_id' => $reserva->id,
                        'transaccion_id' => (string) $transactionId,
                        'codigo_autorizacion' => $authCode ? (string) $authCode : null,
                        'marca_tarjeta' => $brand ? (string) $brand : null,
                        'tarjeta_enmascarada' => $card ? (string) $card : null,
                        'monto' => round((float) $amount, 2),
                        'estado' => 'Authorized',
                        'respuesta_bruta' => [
                            'niubiz' => $response,
                            'voucher' => $reserva->referencia_pago,
                            'meta' => $meta,
                        ],
                    ]);

                    Pago::create([
                        'transaccion_id' => $transaccion->id,
                        'monto' => round((float) $amount, 2),
                        'pagado_en' => now('UTC'),
                        'acepto_terminos' => (bool) session('pago_acepto_terminos', true),
                    ]);
                });

                $reserva->refresh();
                app(ReservaCorreoService::class)->enviarConfirmacionPago(
                    $reserva,
                    $meta,
                    $usuarioNuevo,
                    is_string($usuarioLogin) ? $usuarioLogin : null,
                    is_string($clavePlana) ? $clavePlana : null,
                );

                session()->forget([
                    'pago_reserva_id',
                    'pago_acepto_terminos',
                    'pago_meta',
                    'pago_return_query',
                    'pago_usuario_nuevo',
                    'pago_usuario_login',
                    'pago_clave_plana',
                ]);

                Log::channel('niubiz')->info("[Verify] Pago autorizado reserva #{$reserva->id}");

                if (ReservaFlow::desdePortalActivo()) {
                    ReservaFlow::limpiarMarcaPortal();

                    return redirect()
                        ->route('mis-pagos.index')
                        ->with('success', '¡Pago completado! Tu reserva quedó confirmada.');
                }

                return redirect('/?reserva='.$reserva->id.'&pago=ok&voucher='.urlencode((string) $reserva->referencia_pago))
                    ->with('success', '¡Pago completado con éxito!');
            }

            Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => (string) $transactionId,
                'codigo_autorizacion' => $authCode ? (string) $authCode : null,
                'marca_tarjeta' => $brand ? (string) $brand : null,
                'tarjeta_enmascarada' => $card ? (string) $card : null,
                'monto' => round((float) $amount, 2),
                'estado' => 'Denied',
                'respuesta_bruta' => [
                    'niubiz' => $response,
                    'action_code' => $actionCode,
                ],
            ]);

            $reserva->update(['estado' => 'pago_fallido']);

            $friendly = data_get($response, 'dataMap.ACTION_DESCRIPTION')
                ?? data_get($response, 'data.ACTION_DESCRIPTION')
                ?? 'El pago fue denegado.';

            Log::channel('niubiz')->warning("[Verify] Pago denegado #{$reserva->id}", [
                'action_code' => $actionCode,
            ]);

            return $pagoUrl($friendly);
        } finally {
            optional($lock)->release();
        }
    }
}
