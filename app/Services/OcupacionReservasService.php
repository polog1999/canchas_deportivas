<?php

namespace App\Services;

use App\Support\TurnosOcupados;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OcupacionReservasService
{
    /**
     * Horas ocupadas por cancha según las reservas confirmadas, tomando el
     * turno vigente de cada una (que puede venir de una reprogramación).
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

        foreach ($ocupados as $id => $horas) {
            $ocupados[$id] = array_values(array_unique($horas));
            sort($ocupados[$id]);
        }

        return $ocupados;
    }
}
