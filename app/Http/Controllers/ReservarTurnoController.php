<?php

namespace App\Http\Controllers;

use App\Models\Deporte;
use App\Models\Sede;
use App\Models\TipoUsoTusne;
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

        $deporteModel = Deporte::find($deporteId);
        $deporteNombre = $deporteModel ? $deporteModel->nombre : 'Deporte';

        // 1. Resolver los tipos de espacio permitidos según el deporte elegido
        $espaciosPermitidos = $this->resolverEspaciosPorDeporte($deporteNombre);

        $canchaIds = $sede->canchas->pluck('id');
        $ocupadosPorCancha = $ocupacion->porCancha($canchaIds, $fecha);

        $catalogoTiposUso = TipoUsoTusne::where('esta_activo', true)->orderBy('orden')->get()->keyBy('codigo');
        $oracleService = app(OracleService::class);

        $sedeData = [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'direccion' => $sede->direccion,
            'enlace_mapas' => $sede->enlace_mapas,
            'mapa_embed' => method_exists($sede, 'urlMapaEmbed') ? $sede->urlMapaEmbed() : null,
            'imagen' => method_exists($sede, 'urlImagen') ? $sede->urlImagen() : null,
            'hora_inicio' => $sede->hora_inicio ? substr((string) $sede->hora_inicio, 0, 5) : '08:00',
            'hora_fin' => $sede->hora_fin ? substr((string) $sede->hora_fin, 0, 5) : '22:00',
            'canchas' => $sede->canchas->map(function ($c) use ($ocupadosPorCancha, $oracleService, $catalogoTiposUso, $espaciosPermitidos) {

                // 2. Filtrar TUSNEs compatibles con el deporte (incluyendo genéricos tipo_espacio = 'todos')
                $tusnesFiltrados = $c->catalogosTusne->filter(function ($t) use ($espaciosPermitidos) {
                    return in_array($t->tipo_espacio, $espaciosPermitidos, true) || $t->tipo_espacio === 'todos';
                });

                if ($tusnesFiltrados->isEmpty()) {
                    $tusnesFiltrados = $c->catalogosTusne;
                }

                // 3. Mapear TUSNEs con el monto real de Oracle y sus turnos horarios
                $tusnesMapeados = $tusnesFiltrados->map(function ($t) use ($oracleService, $c) {
                    $montoOracle = null;
                    if (!empty($t->grupo_tusne) && !empty($t->codigo_tusne)) {
                        try {
                            $montoObj = $oracleService->getMontoTusne((string)$t->grupo_tusne, (string)$t->codigo_tusne);
                            $montoVal = $montoObj->conmonto ?? $montoObj->CONMONTO ?? null;
                            if ($montoVal !== null) {
                                $montoOracle = (float)$montoVal;
                            }
                        } catch (\Throwable $th) {}
                    }

                    return [
                        'id' => $t->id,
                        'grupo' => $t->grupo_tusne,
                        'codigo' => $t->codigo_tusne,
                        'descripcion' => $t->descripcion_local,
                        'tipo_espacio' => $t->tipo_espacio,
                        'tipo_uso' => $t->tipo_uso,
                        'horario_turno' => $t->horario_turno, // 'dia', 'noche', 'madrugada_especial', 'todos'
                        'tipo_cliente' => $t->tipo_cliente,
                        'precio_hora' => $montoOracle !== null ? $montoOracle : (float)$c->precio_por_hora,
                    ];
                })->values();

                // 4. Extraer modalidades disponibles
                $codigosUsoDisponibles = $tusnesMapeados->pluck('tipo_uso')->unique()->values();
                $modalidadesDisponibles = $codigosUsoDisponibles->map(function ($codigo) use ($catalogoTiposUso) {
                    if (isset($catalogoTiposUso[$codigo])) {
                        $item = $catalogoTiposUso[$codigo];
                        return [
                            'codigo' => $item->codigo,
                            'nombre' => $item->nombre,
                            'descripcion' => $item->descripcion,
                            'icono' => $item->icono,
                        ];
                    }
                    return [
                        'codigo' => $codigo,
                        'nombre' => ucfirst(str_replace('_', ' ', (string) $codigo)),
                        'descripcion' => 'Uso específico',
                        'icono' => 'fa-futbol',
                    ];
                })->values();

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'detalle' => $c->deportes->pluck('nombre')->implode(' · ') ?: 'Cancha',
                    'deporte_ids' => $c->deportes->pluck('id')->values(),
                    'precio' => (float) $c->precio_por_hora,
                    'ocupados' => $ocupadosPorCancha[$c->id] ?? [],
                    'tusnes' => $tusnesMapeados,
                    'modalidades_disponibles' => $modalidadesDisponibles,
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

    private function resolverEspaciosPorDeporte(string $deporteNombre): array
    {
        $deporteUpper = mb_strtoupper($deporteNombre);

        if (str_contains($deporteUpper, 'FUTSAL') || str_contains($deporteUpper, 'SALA') || str_contains($deporteUpper, 'FULBITO')) {
            return ['losa_futsal', 'losa_general', 'todos'];
        }

        if (str_contains($deporteUpper, 'VOLEY') || str_contains($deporteUpper, 'VÓLEY') || str_contains($deporteUpper, 'VOLEIBOL') ||
            str_contains($deporteUpper, 'BASQUET') || str_contains($deporteUpper, 'BÁSQUET') || str_contains($deporteUpper, 'BALONCESTO')) {
            return ['losa_voley_basquet', 'losa_general', 'todos'];
        }

        if (str_contains($deporteUpper, 'FUTBOL') || str_contains($deporteUpper, 'FÚTBOL') || str_contains($deporteUpper, 'GRASS') || str_contains($deporteUpper, 'SINTETICO') || str_contains($deporteUpper, 'SINTÉTICO')) {
            return ['grass_sintetico', 'todos'];
        }

        if (str_contains($deporteUpper, 'FRONTON') || str_contains($deporteUpper, 'FRONTÓN')) {
            return ['fronton', 'todos'];
        }

        if (str_contains($deporteUpper, 'TENIS')) {
            return ['tenis', 'todos'];
        }

        return ['grass_sintetico', 'losa_voley_basquet', 'losa_futsal', 'fronton', 'tenis', 'losa_general', 'todos'];
    }
}