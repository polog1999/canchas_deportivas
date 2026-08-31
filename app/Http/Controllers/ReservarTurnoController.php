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

        // Sin deporte → volver a deportes
        if ($deporteId <= 0) {
            return redirect()->route('reservar.deporte', ['sede' => $sedeId, 'fecha' => $fecha]);
        }

        $sede = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => function ($q) use ($deporteId) {
                    $q->where('esta_activo', true)
                        ->with(['deportes', 'catalogosTusne' => fn($t) => $t->where('esta_activo', true)])
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

        // Servicio Oracle para consultar los montos reales de cada TUSNE
        $oracleService = app(OracleService::class);

        $sedeData = [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'direccion' => $sede->direccion,
            'imagen' => method_exists($sede, 'urlImagen') ? $sede->urlImagen() : null,
            'hora_inicio' => $sede->hora_inicio ? substr((string) $sede->hora_inicio, 0, 5) : '08:00',
            'hora_fin' => $sede->hora_fin ? substr((string) $sede->hora_fin, 0, 5) : '22:00',
            'canchas' => $sede->canchas->map(function ($c) use ($ocupadosPorCancha, $oracleService) {
                
                // Mapear todos los TUSNEs asociados a esta cancha con su monto de Oracle
                $tusnesMapeados = $c->catalogosTusne->map(function ($t) use ($oracleService, $c) {
                    $montoOracle = null;
                    if (!empty($t->grupo_tusne) && !empty($t->codigo_tusne)) {
                        try {
                            $montoObj = $oracleService->getMontoTusne((string)$t->grupo_tusne, (string)$t->codigo_tusne);
                            $montoVal = $montoObj->conmonto ?? $montoObj->CONMONTO ?? null;
                            if ($montoVal !== null) {
                                $montoOracle = (float)$montoVal;
                            }
                        } catch (\Throwable $th) {
                            // En caso de caída de conexión
                        }
                    }

                    return [
                        'id' => $t->id,
                        'grupo' => $t->grupo_tusne,
                        'codigo' => $t->codigo_tusne,
                        'descripcion' => $t->descripcion_local,
                        'tipo_espacio' => $t->tipo_espacio,
                        'tipo_uso' => $t->tipo_uso,             // 'alquiler_regular', 'campeonato_corporativo', 'liga_oficial', etc.
                        'horario_turno' => $t->horario_turno,   // 'dia', 'noche', 'madrugada_especial', 'todos'
                        'tipo_cliente' => $t->tipo_cliente,     // 'general', 'vecino', etc.
                        'tiene_taquilla' => (bool)$t->tiene_taquilla,
                        'precio_hora' => $montoOracle !== null ? $montoOracle : (float)$c->precio_por_hora,
                    ];
                })->values();

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'detalle' => $c->deportes->pluck('nombre')->implode(' · ') ?: 'Cancha',
                    'deporte_ids' => $c->deportes->pluck('id')->values(),
                    'precio' => (float) $c->precio_por_hora,
                    'ocupados' => $ocupadosPorCancha[$c->id] ?? [],
                    'tusnes' => $tusnesMapeados, // Colección completa de TUSNEs asociados
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