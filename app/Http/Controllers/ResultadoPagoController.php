<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Support\ReservaFlow;
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

        if ($reservaId > 0) {
            $reserva = Reserva::query()
                ->with(['cancha.sede', 'cancha.deportes'])
                ->find($reservaId);
        }

        $mensaje = trim((string) ($request->query('mensaje') ?? session('pago_resultado_mensaje', '')));
        $voucher = trim((string) ($request->query('voucher', $reserva?->referencia_pago ?? '')));

        if (in_array($estado, ['exitoso', 'denegado', 'error'], true)) {
            ReservaFlow::limpiarMarcaPortal();
        }

        return view('reservar-resultado', compact(
            'estado',
            'reserva',
            'mensaje',
            'voucher',
            'desdePortal',
        ));
    }
}
