<?php

namespace App\Services;

use App\Support\TurnosOcupados;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OcupacionReservasService
{
    public function __construct(
        private readonly ReservaCheckoutService $checkout,
    ) {}

    /**
     * Horas ocupadas por cancha: reservas confirmadas (en su turno vigente,
     * que puede venir de una reprogramación) y turnos retenidos por un
     * checkout en curso (alguien pagando en este momento).
     *
     * @param  Collection<int, int>|array<int>  $canchaIds
     * @return array<int, list<int>>
     */
    public function porCancha($canchaIds, string $fecha): array
    {
        $canchaIds = collect($canchaIds)->filter()->values();
        if ($canchaIds->isEmpty()) {
            return [];
        }

        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = $inicioDia->copy()->endOfDay();

        $ocupados = [];
        foreach ($canchaIds as $id) {
            $ocupados[(int) $id] = [];
        }

        foreach (TurnosOcupados::enRango($inicioDia, $finDia, $canchaIds) as $turno) {
            if (! isset($ocupados[$turno->cancha_id])) {
                continue;
            }

            for ($h = 0; $h < 24; $h++) {
                $slotInicio = $inicioDia->copy()->setTime($h, 0, 0);
                $slotFin = $slotInicio->copy()->addHour();

                if ($turno->hora_inicio->lt($slotFin) && $turno->hora_fin->gt($slotInicio)) {
                    $ocupados[$turno->cancha_id][] = $h;
                }
            }
        }

        foreach ($this->checkout->horasRetenidasPorCancha($canchaIds, $fecha) as $canchaId => $horas) {
            if (! isset($ocupados[$canchaId])) {
                continue;
            }

            $ocupados[$canchaId] = array_merge($ocupados[$canchaId], $horas);
        }

        foreach ($ocupados as $id => $horas) {
            $ocupados[$id] = array_values(array_unique($horas));
            sort($ocupados[$id]);
        }

        return $ocupados;
    }
}
