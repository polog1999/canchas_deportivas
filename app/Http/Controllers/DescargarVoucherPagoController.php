<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Usuario;
use App\Services\ReservaVoucherService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DescargarVoucherPagoController extends Controller
{
    public function __invoke(int $pago, ReservaVoucherService $voucherService): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            abort(403);
        }

        $pagoModel = Pago::query()
            ->with([
                'transaccion.reserva.usuario',
            ])
            ->whereHas('transaccion.reserva', fn ($q) => $q->whereRaw('LOWER(estado) = ?', ['confirmada']))
            ->findOrFail($pago);

        $reserva = $pagoModel->transaccion?->reserva;
        $esAdmin = $usuario->tieneRol('admin', 'ADMIN', 'SUPERADMIN');

        if (! $esAdmin && (int) $reserva?->usuario_id !== (int) $usuario->id) {
            abort(403);
        }

        $datos = $voucherService->datosDesdePago($pagoModel);

        return $voucherService->descargar($datos);
    }
}
