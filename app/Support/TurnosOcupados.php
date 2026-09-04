<?php

namespace App\Support;

use App\Models\Reprogramacion;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Única fuente de verdad sobre qué turnos están tomados.
 *
 * La fila de `reservas` nunca se modifica al reprogramar: guarda lo que se
 * pagó. Por eso una reserva reprogramada deja de ocupar su horario original y
 * pasa a ocupar el de su última reprogramación.
 */
class TurnosOcupados
{
    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int>|null  $canchaIds  null = todas
     * @param  list<string>  $estados
     * @return Collection<int, object{reserva_id: int, cancha_id: int, hora_inicio: Carbon, hora_fin: Carbon, reprogramada: bool}>
     */
    public static function enRango(
        Carbon $desde,
        Carbon $hasta,
        $canchaIds = null,
        array $estados = ['confirmada'],
        ?int $excluirReservaId = null,
    ): Collection {
        $ids = $canchaIds === null
            ? null
            : collect($canchaIds)->filter()->map(fn ($id) => (int) $id)->values();

        $filtrarEstado = function ($query, string $columna = 'estado') use ($estados) {
            $query->where(function ($q) use ($estados, $columna) {
                foreach ($estados as $estado) {
                    $q->orWhereRaw('LOWER('.$columna.') = ?', [mb_strtolower($estado)]);
                }
            });
        };

        // Reservas que siguen en su turno original (nunca se reprogramaron).
        $reservas = Reserva::query()
            ->whereDoesntHave('reprogramaciones')
            ->tap(fn ($q) => $filtrarEstado($q))
            ->when($ids !== null, fn ($q) => $q->whereIn('cancha_id', $ids))
            ->when($excluirReservaId, fn ($q) => $q->whereKeyNot($excluirReservaId))
            ->where('hora_inicio', '<', $hasta)
            ->where('hora_fin', '>', $desde)
            ->get(['id', 'cancha_id', 'hora_inicio', 'hora_fin'])
            ->map(fn (Reserva $r) => (object) [
                'reserva_id' => (int) $r->id,
                'cancha_id' => (int) $r->cancha_id,
                'hora_inicio' => Carbon::parse($r->hora_inicio),
                'hora_fin' => Carbon::parse($r->hora_fin),
                'reprogramada' => false,
            ]);

        // Reservas movidas: vale el turno de su última reprogramación.
        $reprogramadas = Reprogramacion::query()
            ->vigentes()
            ->whereHas('reserva', fn ($q) => $filtrarEstado($q))
            ->when($ids !== null, fn ($q) => $q->whereIn('cancha_nueva_id', $ids))
            ->when($excluirReservaId, fn ($q) => $q->where('reserva_id', '!=', $excluirReservaId))
            ->where('hora_inicio_nueva', '<', $hasta)
            ->where('hora_fin_nueva', '>', $desde)
            ->get(['reserva_id', 'cancha_nueva_id', 'hora_inicio_nueva', 'hora_fin_nueva'])
            ->map(fn (Reprogramacion $r) => (object) [
                'reserva_id' => (int) $r->reserva_id,
                'cancha_id' => (int) $r->cancha_nueva_id,
                'hora_inicio' => Carbon::parse($r->hora_inicio_nueva),
                'hora_fin' => Carbon::parse($r->hora_fin_nueva),
                'reprogramada' => true,
            ]);

        return $reservas->concat($reprogramadas)->values();
    }

    /**
     * ¿Hay algo tomando esa cancha en ese rango?
     *
     * @param  list<string>  $estados
     */
    public static function hayChoque(
        int $canchaId,
        Carbon $horaInicio,
        Carbon $horaFin,
        array $estados = ['confirmada'],
        ?int $excluirReservaId = null,
    ): bool {
        return self::enRango($horaInicio, $horaFin, [$canchaId], $estados, $excluirReservaId)
            ->isNotEmpty();
    }
}
