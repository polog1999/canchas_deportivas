<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PreparaReservaDatos;
use App\Models\Sede;
use App\Services\OcupacionReservasService;
use App\Support\ReservaFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalReservarController extends Controller
{
    use PreparaReservaDatos;

    public function index(): View
    {
        ReservaFlow::marcarDesdePortal();

        $sedes = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => fn ($q) => $q
                    ->where('esta_activo', true)
                    ->with('deportes'),
            ])
            ->orderBy('nombre')
            ->get();

        // Solo los deportes que alguna sede activa ofrece, para que el filtro
        // no muestre opciones que dejarían la grilla vacía.
        $deportes = $sedes
            ->flatMap(fn (Sede $sede) => $sede->canchas->flatMap->deportes)
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        return view('portal.reservar.index', compact('sedes', 'deportes'));
    }

    public function deporte(Request $request): View|RedirectResponse
    {
        ReservaFlow::marcarDesdePortal();

        $datos = $this->datosDeporte($request, 'portal.reservar.index');
        if ($datos instanceof RedirectResponse) {
            return $datos;
        }

        return view('portal.reservar.deporte', $datos);
    }

    public function turno(Request $request, OcupacionReservasService $ocupacion): View|RedirectResponse
    {
        ReservaFlow::marcarDesdePortal();

        $datos = $this->datosTurno(
            $request,
            $ocupacion,
            'portal.reservar.index',
            'portal.reservar.deporte',
        );

        if ($datos instanceof RedirectResponse) {
            return $datos;
        }

        return view('portal.reservar.turno', $datos);
    }

    public function confirmar(): View
    {
        ReservaFlow::marcarDesdePortal();

        return view('portal.reservar.confirmar', $this->datosConfirmar(desdeSesion: true));
    }

    public function pago(): View
    {
        ReservaFlow::marcarDesdePortal();

        return view('portal.reservar.pago');
    }
}
