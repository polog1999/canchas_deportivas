<?php

namespace App\Http\Controllers;

use App\Models\Deporte;
use App\Models\Sede;
use App\Services\OcupacionReservasService;
use App\Services\OracleService;
use Illuminate\Http\Request;

class ReservarTurnoController extends Controller
{
    public function __invoke(Request $request, OcupacionReservasService $ocupacion)
    {
        $sedeId = (int) $request->query('sede', 0);
        $deporteId = (int) $request->query('deporte_id', 0);
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        // Sin deporte → deportes de la sede elegida
        if ($deporteId <= 0) {
            return redirect()->route('reservar.deporte', [
                'sede' => $sedeId,
                'fecha' => $fecha,
            ]);
        }

        $sede = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => function ($q) use ($deporteId) {
                    $q->where('esta_activo', true)
                        ->with(['deportes', 'canchasTusne.catalogoTusne'])
                        ->orderBy('nombre');

                    if ($deporteId > 0) {
                        $q->whereHas('deportes', fn ($d) => $d->where('deportes.id', $deporteId));
                    }
                },
            ])
            ->find($sedeId);

        if (! $sede) {
            return redirect()->route('reservar');
        }

        if ($deporteId > 0) {
            $deporteNombre = (string) (Deporte::query()->where('id', $deporteId)->value('nombre') ?? 'Deporte');
        } else {
            $deporteNombre = $sede->canchas
                ->flatMap(fn ($c) => $c->deportes->pluck('nombre'))
                ->unique()
                ->values()
                ->implode(' · ') ?: 'Deporte';
        }

        $canchaIds = $sede->canchas->pluck('id');
        $ocupadosPorCancha = $ocupacion->porCancha($canchaIds, $fecha);

        // Instanciar el servicio Oracle usando app()
        $oracleService = app(OracleService::class);

        $sedeData = [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'direccion' => $sede->direccion,
            'imagen' => method_exists($sede, 'urlImagen') ? $sede->urlImagen() : null,
            'hora_inicio' => $sede->hora_inicio ? substr((string) $sede->hora_inicio, 0, 5) : '08:00',
            'hora_fin' => $sede->hora_fin ? substr((string) $sede->hora_fin, 0, 5) : '22:00',
            'canchas' => $sede->canchas->map(function ($c) use ($ocupadosPorCancha, $oracleService) {
                
                // Precio base local por defecto
                $precio = (float) $c->precio_por_hora;

                // Obtener el TUSNE vinculado a la cancha
                $canchaTusne = $c->canchasTusne->first();
                $catalogoTusne = $canchaTusne?->catalogoTusne;

                if ($catalogoTusne && !empty($catalogoTusne->grupo_tusne) && !empty($catalogoTusne->codigo_tusne)) {
                    try {
                        // Consulta del monto a Oracle
                        $montoOracle = $oracleService->getMontoTusne(
                            (string) $catalogoTusne->grupo_tusne,
                            (string) $catalogoTusne->codigo_tusne
                        );

                        $monto = $montoOracle->conmonto ?? $montoOracle->CONMONTO ?? null;
                        if ($monto !== null) {
                            $precio = (float) $monto;
                        }
                    } catch (\Throwable $th) {
                        // En caso de error de conexión mantiene el precio base local
                    }
                }

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'detalle' => $c->deportes->pluck('nombre')->implode(' · ') ?: 'Cancha',
                    'deporte_ids' => $c->deportes->pluck('id')->values(),
                    'precio' => $precio,
                    'ocupados' => $ocupadosPorCancha[$c->id] ?? [],
                ];
            })->values(),
        ];

        return view('reservar-turno', [
            'sede' => $sedeData,
            'fecha' => $fecha,
            'deporte' => $deporteNombre,
            'deporte_id' => $deporteId > 0 ? $deporteId : null,
        ]);
    }
}