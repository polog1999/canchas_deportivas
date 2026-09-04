<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\CatalogoTusne;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Calcula en el servidor la tarifa de un turno.
 *
 * Replica la regla que la grilla aplica en el navegador: el monto sale del
 * TUSNE vigente (consultado en Oracle) y, si Oracle no responde, del precio
 * por hora de la cancha.
 */
class TarifaTusneService
{
    private const CACHE_TTL = 300;

    public function __construct(
        private readonly OracleService $oracle,
    ) {}

    /**
     * Turno horario al que pertenece una hora del día.
     */
    public function turnoDeHora(int $hora): string
    {
        return $hora >= 18 ? 'noche' : 'dia';
    }

    /**
     * ¿Este TUSNE cubre el turno de esa hora?
     */
    public function tusneAplicaEnHora(CatalogoTusne $tusne, Carbon $hora): bool
    {
        $turno = $this->turnoDeHora((int) $hora->format('G'));

        return $tusne->horario_turno === $turno || $tusne->horario_turno === 'todos';
    }

    /**
     * ¿Este TUSNE cubre todas las horas que abarca el turno?
     */
    public function tusneCubreRango(CatalogoTusne $tusne, Carbon $horaInicio, Carbon $horaFin): bool
    {
        $cursor = $horaInicio->copy()->startOfHour();

        while ($cursor->lt($horaFin)) {
            if (! $this->tusneAplicaEnHora($tusne, $cursor)) {
                return false;
            }

            $cursor->addHour();
        }

        return true;
    }

    /**
     * Precio por hora del TUSNE, con el precio de la cancha como respaldo.
     */
    public function precioHora(Cancha $cancha, ?CatalogoTusne $tusne): float
    {
        $fallback = round((float) $cancha->precio_por_hora, 2);

        if (! $tusne || empty($tusne->grupo_tusne) || empty($tusne->codigo_tusne)) {
            return $fallback;
        }

        $clave = 'tusne_monto:'.$tusne->grupo_tusne.':'.$tusne->codigo_tusne;

        $monto = Cache::remember($clave, self::CACHE_TTL, function () use ($tusne) {
            try {
                $fila = $this->oracle->getMontoTusne(
                    (string) $tusne->grupo_tusne,
                    (string) $tusne->codigo_tusne,
                );
            } catch (\Throwable) {
                return null;
            }

            $valor = $fila->conmonto ?? $fila->CONMONTO ?? null;

            return $valor !== null ? (float) $valor : null;
        });

        return $monto !== null ? round($monto, 2) : $fallback;
    }

    /**
     * Monto total de un turno de N horas.
     */
    public function precioTotal(Cancha $cancha, ?CatalogoTusne $tusne, float $horas): float
    {
        return round($this->precioHora($cancha, $tusne) * $horas, 2);
    }
}
