<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Cancha;
use App\Models\Deporte;
use App\Models\Distrito;
use App\Models\Sede;
use App\Services\OcupacionReservasService;
use App\Services\OracleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait PreparaReservaDatos
{
    /**
     * @return array{sede: \App\Models\Sede, deportes: \Illuminate\Support\Collection, fecha: string}|RedirectResponse
     */
    protected function datosDeporte(Request $request, string $indexRoute): array|RedirectResponse
    {
        $sedeId = (int) $request->query('sede', 0);
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        $sede = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => fn ($q) => $q
                    ->where('esta_activo', true)
                    ->with('deportes'),
            ])
            ->find($sedeId);

        if (! $sede) {
            return redirect()->route($indexRoute);
        }

        $deportes = $sede->canchas
            ->flatMap(fn ($c) => $c->deportes->map(fn ($d) => [
                'deporte' => $d,
                'precio' => (float) $c->precio_por_hora,
            ]))
            ->groupBy(fn ($row) => $row['deporte']->id)
            ->map(function ($rows) {
                /** @var \App\Models\Deporte $deporte */
                $deporte = $rows->first()['deporte'];
                $precios = $rows->pluck('precio')->filter(fn ($p) => $p > 0);

                return [
                    'id' => $deporte->id,
                    'nombre' => $deporte->nombre,
                    'imagen_url' => $deporte->urlImagen(),
                    'canchas' => $rows->count(),
                    'precioDesde' => $precios->isNotEmpty() ? (float) $precios->min() : 0,
                ];
            })
            ->sortBy('nombre')
            ->values();

        return compact('sede', 'deportes', 'fecha');
    }

    /**
     * @return array{sede: array<string, mixed>, fecha: string, deporte: string, deporte_id: int|null}|RedirectResponse
     */
    protected function datosTurno(
        Request $request,
        OcupacionReservasService $ocupacion,
        string $indexRoute,
        string $deporteRoute,
    ): array|RedirectResponse {
        $sedeId = (int) $request->query('sede', 0);
        $deporteId = (int) $request->query('deporte_id', 0);
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
            $fecha = now()->format('Y-m-d');
        }

        if ($deporteId <= 0) {
            return redirect()->route($deporteRoute, ['sede' => $sedeId, 'fecha' => $fecha]);
        }

        $sede = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => function ($q) use ($deporteId) {
                    $q->where('esta_activo', true)
                        ->with(['deportes', 'catalogosTusne' => fn ($t) => $t->where('esta_activo', true)])
                        ->orderBy('nombre')
                        ->whereHas('deportes', fn ($d) => $d->where('deportes.id', $deporteId));
                },
            ])
            ->find($sedeId);

        if (! $sede) {
            return redirect()->route($indexRoute);
        }

        $deporteNombre = (string) (Deporte::query()->where('id', $deporteId)->value('nombre') ?? 'Deporte');

        $canchaIds = $sede->canchas->pluck('id');
        $ocupadosPorCancha = $ocupacion->porCancha($canchaIds, $fecha);
        $oracleService = app(OracleService::class);

        $sedeData = [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'direccion' => $sede->direccion,
            'enlace_mapas' => $sede->enlace_mapas,
            'mapa_embed' => $sede->urlMapaEmbed(),
            'imagen' => method_exists($sede, 'urlImagen') ? $sede->urlImagen() : null,
            'hora_inicio' => $sede->hora_inicio ? substr((string) $sede->hora_inicio, 0, 5) : '08:00',
            'hora_fin' => $sede->hora_fin ? substr((string) $sede->hora_fin, 0, 5) : '22:00',
            'canchas' => $sede->canchas->map(function ($c) use ($ocupadosPorCancha, $oracleService) {
                $tusnesMapeados = $c->catalogosTusne->map(function ($t) use ($oracleService, $c) {
                    $montoOracle = null;
                    if (! empty($t->grupo_tusne) && ! empty($t->codigo_tusne)) {
                        try {
                            $montoObj = $oracleService->getMontoTusne((string) $t->grupo_tusne, (string) $t->codigo_tusne);
                            $montoVal = $montoObj->conmonto ?? $montoObj->CONMONTO ?? null;
                            if ($montoVal !== null) {
                                $montoOracle = (float) $montoVal;
                            }
                        } catch (\Throwable) {
                        }
                    }

                    return [
                        'id' => $t->id,
                        'grupo' => $t->grupo_tusne,
                        'codigo' => $t->codigo_tusne,
                        'descripcion' => $t->descripcion_local,
                        'tipo_espacio' => $t->tipo_espacio,
                        'tipo_uso' => $t->tipo_uso,
                        'horario_turno' => $t->horario_turno,
                        'tipo_cliente' => $t->tipo_cliente,
                        'tiene_taquilla' => (bool) $t->tiene_taquilla,
                        'precio_hora' => $montoOracle !== null ? $montoOracle : (float) $c->precio_por_hora,
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
                ];
            })->values(),
        ];

        return [
            'sede' => $sedeData,
            'fecha' => $fecha,
            'deporte' => $deporteNombre,
            'deporte_id' => $deporteId,
        ];
    }

    /**
     * @return array{distritos: \Illuminate\Database\Eloquent\Collection, usuarioPortal: array<string, mixed>|null}
     */
    protected function datosConfirmar(bool $desdeSesion = false): array
    {
        $distritos = Distrito::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $usuarioPortal = null;

        if ($desdeSesion && Auth::check()) {
            /** @var \App\Models\Usuario $usuario */
            $usuario = Auth::user();
            $usuario->loadMissing('perfil');

            $documento = preg_replace('/\D+/', '', (string) ($usuario->perfil?->numero_documento ?? ''));
            if (strlen($documento) < 8) {
                $documentoLogin = preg_replace('/\D+/', '', (string) $usuario->usuario);
                if (strlen($documentoLogin) >= 8) {
                    $documento = $documentoLogin;
                }
            }

            $usuarioPortal = [
                'tipo_documento_id' => $usuario->perfil?->tipo_documento_id ?: 1,
                'documento' => $documento,
                'documento_editable' => strlen($documento) < 8,
                'nombres' => $usuario->perfil?->nombres ?? '',
                'apellido_paterno' => $usuario->perfil?->apellido_paterno ?? '',
                'apellido_materno' => $usuario->perfil?->apellido_materno ?? '',
                'email' => $usuario->correo_electronico ?? '',
                'distrito_id' => $usuario->perfil?->ubigeo_distrito ?? '',
            ];
        }

        return compact('distritos', 'usuarioPortal');
    }
}
