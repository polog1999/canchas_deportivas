<?php
namespace App\Livewire;

use App\Models\Cancha;
use App\Models\Deporte;
use App\Models\Reserva;
use App\Models\Sede;
use App\Services\OracleService;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class ReservarTurno extends Component
{
    #[Url(as: 'sede')]
    public $sedeId = 0;

    #[Url(as: 'deporte_id')]
    public $deporteId = 0;

    #[Url(as: 'fecha')]
    public $fecha = '';

    public function mount()
    {
        $this->sedeId = (int) request()->query('sede', $this->sedeId);
        $this->deporteId = (int) request()->query('deporte_id', $this->deporteId);
        
        $fechaParam = request()->query('fecha', $this->fecha);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fechaParam)) {
            $fechaParam = now()->format('Y-m-d');
        }
        $this->fecha = $fechaParam;

        if ($this->deporteId <= 0) {
            return redirect()->route('reservar.deporte', [
                'sede' => $this->sedeId,
                'fecha' => $this->fecha,
            ]);
        }
    }

    public function render()
    {
        $sede = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => function ($q) {
                    $q->where('esta_activo', true)
                        ->with(['deportes', 'canchasTusne.catalogoTusne'])
                        ->orderBy('nombre');

                    if ($this->deporteId > 0) {
                        $q->whereHas('deportes', fn ($d) => $d->where('deportes.id', $this->deporteId));
                    }
                },
            ])
            ->find($this->sedeId);

        if (!$sede) {
            return redirect()->route('reservar');
        }

        $deporteNombre = (string) (Deporte::query()->where('id', $this->deporteId)->value('nombre') ?? 'Deporte');

        $canchaIds = $sede->canchas->pluck('id');
        $ocupadosPorCancha = $this->ocupacionReservasPorCancha($canchaIds, $this->fecha);

        // Servicio Oracle
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
                
                $precioFinal = (float) $c->precio_por_hora;

                $canchaTusne = $c->canchasTusne->first();
                $catalogoTusne = $canchaTusne?->catalogoTusne;

                if ($catalogoTusne && !empty($catalogoTusne->grupo_tusne) && !empty($catalogoTusne->codigo_tusne)) {
                    try {
                        $montoOracle = $oracleService->getMontoTusne(
                            (string) $catalogoTusne->grupo_tusne,
                            (string) $catalogoTusne->codigo_tusne
                        );

                        $montoDetectado = $montoOracle->conmonto ?? $montoOracle->CONMONTO ?? null;

                        if ($montoDetectado !== null) {
                            $precioFinal = (float) $montoDetectado;
                        }
                    } catch (\Throwable $th) {
                        // Mantiene precio local en caso de desconexión
                    }
                }

                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'detalle' => $c->deportes->pluck('nombre')->implode(' · ') ?: 'Cancha',
                    'deporte_ids' => $c->deportes->pluck('id')->values(),
                    'precio' => $precioFinal,
                    'ocupados' => $ocupadosPorCancha[$c->id] ?? [],
                ];
            })->values(),
        ];

        // Se apunta a 'reservar-turno' directamente en views
        return view('reservar-turno', [
            'sede' => $sedeData,
            'fecha' => $this->fecha,
            'deporte' => $deporteNombre,
            'deporte_id' => $this->deporteId > 0 ? $this->deporteId : null,
        ]);
    }

    private function ocupacionReservasPorCancha($canchaIds, string $fecha): array
    {
        $canchaIds = collect($canchaIds)->filter()->values();
        if ($canchaIds->isEmpty()) {
            return [];
        }

        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = $inicioDia->copy()->endOfDay();

        $reservas = Reserva::query()
            ->whereIn('cancha_id', $canchaIds)
            ->where('hora_inicio', '<', $finDia)
            ->where('hora_fin', '>', $inicioDia)
            ->where(function ($q) {
                $q->whereNull('estado')
                    ->orWhereRaw('UPPER(estado) <> ?', ['CANCELADA']);
            })
            ->get(['cancha_id', 'hora_inicio', 'hora_fin']);

        $ocupados = [];
        foreach ($canchaIds as $id) {
            $ocupados[(int) $id] = [];
        }

        foreach ($reservas as $reserva) {
            $inicio = Carbon::parse($reserva->hora_inicio);
            $fin = Carbon::parse($reserva->hora_fin);

            for ($h = 0; $h < 24; $h++) {
                $slotInicio = $inicioDia->copy()->setTime($h, 0, 0);
                $slotFin = $slotInicio->copy()->addHour();

                if ($inicio->lt($slotFin) && $fin->gt($slotInicio)) {
                    $ocupados[(int) $reserva->cancha_id][] = $h;
                }
            }
        }

        foreach ($ocupados as $id => $horas) {
            $ocupados[$id] = array_values(array_unique($horas));
            sort($ocupados[$id]);
        }

        return $ocupados;
    }
}