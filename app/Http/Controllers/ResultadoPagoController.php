<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Support\ReservaFlow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultadoPagoController extends Controller
{
    public function __invoke(Request $request): View
    {
        $estado = $request->query('estado', 'procesando');
        if (! in_array($estado, ['exitoso', 'denegado', 'error', 'procesando'], true)) {
            $estado = 'procesando';
        }

        $desdePortal = ReservaFlow::desdePortalActivo() || $request->routeIs('portal.reservar.resultado');

        $reservaId = (int) $request->query('reserva', 0);
        $reserva = null;
        $transaccion = null;
        $comprobante = [
            'numero_pedido' => '',
            'voucher' => '',
            'titular' => '—',
            'fecha_pedido_label' => '—',
            'importe_label' => '—',
            'moneda_label' => 'Soles (PEN)',
            'descripcion_producto' => '—',
            'descripcion_denegacion' => '',
        ];

        if ($reservaId > 0) {
            $reserva = Reserva::query()
                ->with(['cancha.sede', 'cancha.deportes', 'usuario.perfil'])
                ->find($reservaId);

            if ($reserva) {
                $transaccion = $reserva->transacciones()
                    ->latest('id')
                    ->first();

                $comprobante = $this->armarComprobante(
                    $reserva,
                    $transaccion,
                    $estado,
                    trim((string) ($request->query('mensaje') ?? session('pago_resultado_mensaje', ''))),
                    trim((string) ($request->query('voucher', $reserva->referencia_pago ?? ''))),
                );
            }
        }

        $mensaje = $comprobante['descripcion_denegacion'] ?? '';
        $voucher = $comprobante['voucher'] ?? '';

        if (in_array($estado, ['exitoso', 'denegado', 'error'], true)) {
            ReservaFlow::limpiarMarcaPortal();
        }

        return view('reservar-resultado', compact(
            'estado',
            'reserva',
            'transaccion',
            'comprobante',
            'mensaje',
            'voucher',
            'desdePortal',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function armarComprobante(
        Reserva $reserva,
        ?\App\Models\Transaccion $transaccion,
        string $estado,
        string $mensaje,
        string $voucher,
    ): array {
        $usuario = $reserva->usuario;
        $perfil = $usuario?->perfil;

        $titular = trim((string) ($perfil?->nombreCompleto() ?: $usuario?->nombreParaMostrar() ?: ''));
        if ($titular === '' || $titular === 'Mi cuenta') {
            $titular = '—';
        }

        $fechaPedido = $transaccion?->creado_en
            ?? $reserva->actualizado_en
            ?? $reserva->creado_en
            ?? now();

        if (! $fechaPedido instanceof Carbon) {
            $fechaPedido = Carbon::parse($fechaPedido);
        }

        $fechaPedido = $fechaPedido->timezone('America/Lima');

        $descripcionProducto = $this->descripcionProducto($reserva);
        $monto = round((float) ($transaccion?->monto ?? $reserva->precio_total), 2);

        $descripcionDenegacion = $mensaje;
        if ($descripcionDenegacion === '' && $transaccion) {
            $descripcionDenegacion = (string) (
                data_get($transaccion->respuesta_bruta, 'niubiz.dataMap.ACTION_DESCRIPTION')
                ?? data_get($transaccion->respuesta_bruta, 'niubiz.data.ACTION_DESCRIPTION')
                ?? data_get($transaccion->respuesta_bruta, 'action_code')
                ?? ''
            );
        }

        if ($descripcionDenegacion === '' && in_array($estado, ['denegado', 'error'], true)) {
            $descripcionDenegacion = $estado === 'denegado'
                ? 'La transacción fue denegada por el emisor de la tarjeta.'
                : 'No se pudo completar el pago. Intenta nuevamente.';
        }

        return [
            'numero_pedido' => (string) $reserva->id,
            'voucher' => $voucher !== '' ? $voucher : (string) $reserva->referencia_pago,
            'titular' => $titular,
            'fecha_pedido' => $fechaPedido,
            'fecha_pedido_label' => $fechaPedido->translatedFormat('d/m/Y H:i:s'),
            'importe' => $monto,
            'importe_label' => 'S/ '.number_format($monto, 2),
            'moneda' => 'PEN',
            'moneda_label' => 'Soles (PEN)',
            'descripcion_producto' => $descripcionProducto,
            'descripcion_denegacion' => $descripcionDenegacion,
            'marca_tarjeta' => $transaccion?->marca_tarjeta,
            'tarjeta_enmascarada' => $transaccion?->tarjeta_enmascarada,
            'codigo_autorizacion' => $transaccion?->codigo_autorizacion,
        ];
    }

    private function descripcionProducto(Reserva $reserva): string
    {
        $partes = array_filter([
            'Reserva de cancha deportiva',
            $reserva->cancha?->sede?->nombre,
            $reserva->cancha?->nombre,
        ]);

        if ($reserva->hora_inicio) {
            $inicio = $reserva->hora_inicio->timezone('America/Lima');
            $partes[] = 'Turno '.$inicio->format('d/m/Y H:i');
            if ($reserva->hora_fin) {
                $partes[] = 'a '.$reserva->hora_fin->timezone('America/Lima')->format('H:i').' hs';
            }
        }

        return implode(' — ', $partes);
    }
}
