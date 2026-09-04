<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\CatalogoTusne;
use App\Models\Pago;
use App\Models\Reprogramacion;
use App\Models\Reserva;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mueve una reserva a otro turno sin alterar el monto pagado.
 *
 * Solo se admiten turnos donde siga aplicando el mismo TUSNE del pago original
 * y con la misma cantidad de horas: así el monto cuadra por construcción y la
 * liquidación en Oracle sigue correspondiendo al servicio que se va a prestar.
 */
class ReprogramacionReservaService
{
    private const TOLERANCIA = 0.01;

    public function __construct(
        private readonly TarifaTusneService $tarifas,
        private readonly ReservaCheckoutService $checkout,
    ) {}

    /**
     * TUSNE con el que se cobró la reserva.
     */
    public function tusneOriginal(Reserva $reserva): ?CatalogoTusne
    {
        $tusneId = Pago::query()
            ->whereIn('transaccion_id', $reserva->transacciones()->select('id'))
            ->whereNotNull('id_catalogos_tusne')
            ->orderByDesc('id')
            ->value('id_catalogos_tusne');

        return $tusneId ? CatalogoTusne::find($tusneId) : null;
    }

    public function duracionMinutos(Reserva $reserva): int
    {
        $minutos = (int) $reserva->hora_inicio->diffInMinutes($reserva->hora_fin);

        return $minutos > 0 ? $minutos : 60;
    }

    /**
     * Datos base que condicionan la reprogramación.
     *
     * @return array{
     *     tusne: CatalogoTusne|null,
     *     duracion: int,
     *     horas: float,
     *     monto_original: float,
     *     monto_actual: float,
     *     tarifa_cambio: bool
     * }
     */
    public function contexto(Reserva $reserva): array
    {
        $tusne = $this->tusneOriginal($reserva);
        $duracion = $this->duracionMinutos($reserva);
        $horas = $duracion / 60;
        $montoOriginal = round((float) $reserva->precio_total, 2);
        $montoActual = $this->tarifas->precioTotal($reserva->cancha, $tusne, $horas);

        return [
            'tusne' => $tusne,
            'duracion' => $duracion,
            'horas' => $horas,
            'monto_original' => $montoOriginal,
            'monto_actual' => $montoActual,
            'tarifa_cambio' => $montoOriginal > 0
                && abs($montoActual - $montoOriginal) > self::TOLERANCIA,
        ];
    }

    /**
     * Canchas a las que se puede mover: las de la misma sede que tengan
     * asignado el mismo TUSNE.
     *
     * @return \Illuminate\Support\Collection<int, Cancha>
     */
    public function canchasCompatibles(Reserva $reserva, ?CatalogoTusne $tusne): \Illuminate\Support\Collection
    {
        return Cancha::query()
            ->where('sede_id', $reserva->cancha->sede_id)
            ->where('esta_activo', true)
            ->when(
                $tusne,
                fn ($q) => $q->whereHas('catalogosTusne', fn ($t) => $t->where('catalogos_tusne.id', $tusne->id)),
            )
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Grilla de turnos de un día, marcando por qué cada hora se puede usar o no.
     *
     * @return array{
     *     horas: list<int>,
     *     canchas: list<array<string, mixed>>
     * }
     */
    public function grilla(Reserva $reserva, string $fecha): array
    {
        $contexto = $this->contexto($reserva);
        $sede = $reserva->cancha->sede;

        $apertura = $this->horaEntera($sede?->hora_inicio, 8);
        $cierre = $this->horaEntera($sede?->hora_fin, 22);
        $horas = range($apertura, max($apertura, $cierre - 1));

        $canchas = [];

        foreach ($this->canchasCompatibles($reserva, $contexto['tusne']) as $cancha) {
            $slots = [];

            foreach ($horas as $h) {
                $slots[$h] = $this->evaluarSlot($reserva, $contexto, $cancha, $fecha, $h, $cierre);
            }

            $canchas[] = [
                'id' => $cancha->id,
                'nombre' => $cancha->nombre,
                'slots' => $slots,
            ];
        }

        return [
            'horas' => $horas,
            'canchas' => $canchas,
        ];
    }

    /**
     * Cada casilla representa la HORA DE INICIO de un turno de la misma
     * duración que el original, no una hora suelta.
     *
     * @param  array<string, mixed>  $contexto
     * @return array{
     *     estado: string,
     *     motivo: string|null,
     *     precio: float|null,
     *     rango: string,
     *     dentro_actual: bool
     * }
     */
    private function evaluarSlot(
        Reserva $reserva,
        array $contexto,
        Cancha $cancha,
        string $fecha,
        int $hora,
        int $cierre,
    ): array {
        $horaInicio = Carbon::createFromFormat('Y-m-d H:i', $fecha.' '.sprintf('%02d:00', $hora), 'America/Lima');
        $horaFin = $horaInicio->copy()->addMinutes($contexto['duracion']);
        $rango = $horaInicio->format('H:i').' – '.$horaFin->format('H:i');

        // ¿Esta hora del día cae dentro del turno que la reserva ocupa hoy?
        $finDeEstaHora = $horaInicio->copy()->addHour();
        $dentroActual = $cancha->id === $reserva->canchaVigenteId()
            && $reserva->horaInicioVigente()->lt($finDeEstaHora)
            && $reserva->horaFinVigente()->gt($horaInicio);

        $resultado = fn (string $estado, ?string $motivo, ?float $precio) => [
            'estado' => $estado,
            'motivo' => $motivo,
            'precio' => $precio,
            'rango' => $rango,
            'dentro_actual' => $dentroActual,
        ];

        if ($cancha->id === $reserva->canchaVigenteId() && $horaInicio->equalTo($reserva->horaInicioVigente())) {
            return $resultado('actual', 'Turno actual de la reserva: '.$rango, null);
        }

        if ($horaInicio->isPast()) {
            return $resultado('bloqueado', 'Hora ya pasada', null);
        }

        if ((int) $horaFin->format('G') > $cierre || ($horaFin->format('G') == $cierre && (int) $horaFin->format('i') > 0)) {
            return $resultado('bloqueado', 'No entra antes del cierre: ocuparía hasta las '.$horaFin->format('H:i'), null);
        }

        $tusne = $contexto['tusne'];

        if ($tusne && ! $this->tarifas->tusneCubreRango($tusne, $horaInicio, $horaFin)) {
            return $resultado(
                'bloqueado',
                $rango.' · tarifa distinta: el turno pagado es '.$this->etiquetaTurno($tusne->horario_turno),
                null,
            );
        }

        $precio = $this->tarifas->precioTotal($cancha, $tusne, $contexto['horas']);

        if (abs($precio - $contexto['monto_original']) > self::TOLERANCIA) {
            return $resultado(
                'bloqueado',
                $rango.' · no cuadra con el monto pagado: aquí costaría S/ '.number_format($precio, 2),
                $precio,
            );
        }

        if (! $this->checkout->turnoDisponible($cancha->id, $horaInicio, $horaFin, null, $reserva->id)) {
            return $resultado('bloqueado', $rango.' · se cruza con otra reserva', $precio);
        }

        return $resultado('disponible', 'Mover a '.$rango, $precio);
    }

    /**
     * Registra el nuevo turno de la reserva.
     *
     * La fila de `reservas` no se toca: conserva el turno y el monto que se
     * pagaron. A partir de aquí quien manda es este registro, y el turno
     * anterior queda libre porque la ocupación se calcula con ambas tablas.
     */
    public function reprogramar(
        Reserva $reserva,
        int $canchaId,
        Carbon $horaInicio,
        Usuario $autorizador,
        ?string $motivo = null,
    ): Reprogramacion {
        $contexto = $this->contexto($reserva);

        if ($contexto['tarifa_cambio']) {
            throw ValidationException::withMessages([
                'hora' => 'La tarifa cambió desde que se pagó (S/ '
                    .number_format($contexto['monto_original'], 2).' → S/ '
                    .number_format($contexto['monto_actual'], 2)
                    .'). No se puede reprogramar sin ajustar el monto.',
            ]);
        }

        $cancha = Cancha::query()->with('sede')->findOrFail($canchaId);

        if ($cancha->sede_id !== $reserva->cancha->sede_id) {
            throw ValidationException::withMessages([
                'cancha_id' => 'Solo se puede reprogramar dentro de la misma sede.',
            ]);
        }

        $horaFin = $horaInicio->copy()->addMinutes($contexto['duracion']);
        $tusne = $contexto['tusne'];

        if ($horaInicio->isPast()) {
            throw ValidationException::withMessages([
                'hora' => 'No se puede reprogramar a un horario que ya pasó.',
            ]);
        }

        if ($tusne) {
            $perteneceACancha = $cancha->catalogosTusne()
                ->where('catalogos_tusne.id', $tusne->id)
                ->exists();

            if (! $perteneceACancha) {
                throw ValidationException::withMessages([
                    'cancha_id' => 'Esa cancha no tiene la misma tarifa con la que se pagó la reserva.',
                ]);
            }

            if (! $this->tarifas->tusneCubreRango($tusne, $horaInicio, $horaFin)) {
                throw ValidationException::withMessages([
                    'hora' => 'Ese horario corresponde a otro turno ('
                        .$this->etiquetaTurno($tusne->horario_turno).') y el monto no cuadraría.',
                ]);
            }
        }

        $precio = $this->tarifas->precioTotal($cancha, $tusne, $contexto['horas']);

        if (abs($precio - $contexto['monto_original']) > self::TOLERANCIA) {
            throw ValidationException::withMessages([
                'hora' => 'Ese turno cuesta S/ '.number_format($precio, 2)
                    .' y la reserva se pagó S/ '.number_format($contexto['monto_original'], 2).'.',
            ]);
        }

        return DB::transaction(function () use ($reserva, $cancha, $horaInicio, $horaFin, $tusne, $precio, $autorizador, $motivo) {
            // Se bloquea la fila para que nadie reprograme la misma reserva en paralelo.
            $fresca = Reserva::query()
                ->with('reprogramacionVigente')
                ->lockForUpdate()
                ->findOrFail($reserva->id);

            if (! $this->checkout->turnoDisponible($cancha->id, $horaInicio, $horaFin, null, $fresca->id)) {
                throw ValidationException::withMessages([
                    'hora' => 'Ese horario acaba de ser tomado. Elige otro turno.',
                ]);
            }

            // El turno "anterior" es el que estaba vigente, que puede venir de
            // una reprogramación previa y no de la reserva original.
            return Reprogramacion::create([
                'reserva_id' => $fresca->id,
                'cancha_anterior_id' => $fresca->canchaVigenteId(),
                'cancha_nueva_id' => $cancha->id,
                'hora_inicio_anterior' => $fresca->horaInicioVigente(),
                'hora_fin_anterior' => $fresca->horaFinVigente(),
                'hora_inicio_nueva' => $horaInicio,
                'hora_fin_nueva' => $horaFin,
                'monto_validado' => $precio,
                'catalogo_tusne_id' => $tusne?->id,
                'motivo' => $motivo,
                'autorizado_por' => $autorizador->id,
            ]);
        });
    }

    private function etiquetaTurno(?string $turno): string
    {
        return match ($turno) {
            'noche' => 'nocturno',
            'dia' => 'diurno',
            'madrugada_especial' => 'madrugada',
            default => 'todo el día',
        };
    }

    private function horaEntera(mixed $valor, int $porDefecto): int
    {
        $texto = trim((string) $valor);

        if ($texto === '' || ! preg_match('/^(\d{1,2})/', $texto, $m)) {
            return $porDefecto;
        }

        $hora = (int) $m[1];

        return $hora >= 0 && $hora <= 23 ? $hora : $porDefecto;
    }
}
