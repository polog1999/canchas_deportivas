<?php

namespace App\Services;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OcupacionReservasService
{
    /**
     * Horas ocupadas por cancha según reservas (hora_inicio / hora_fin).
     * Un slot H:00–(H+1):00 está ocupado si solapa con alguna reserva no cancelada.
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

        $reservas = Reserva::query()
            ->whereIn('cancha_id', $canchaIds)
            ->where('hora_inicio', '<', $finDia)
            ->where('hora_fin', '>', $inicioDia)
            ->whereRaw('LOWER(estado) = ?', ['confirmada'])
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
