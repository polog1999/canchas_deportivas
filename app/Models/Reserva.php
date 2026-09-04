<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reserva extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'reservas';

    protected $fillable = [
        'usuario_id',
        'cancha_id',
        'hora_inicio',
        'hora_fin',
        'precio_total',
        'referencia_pago',
        'estado',
        'cantidad_horas',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
            'precio_total' => 'decimal:2',
            'cantidad_horas' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class, 'cancha_id');
    }

    public function transacciones(): HasMany
    {
        return $this->hasMany(Transaccion::class, 'reserva_id');
    }

    public function reprogramaciones(): HasMany
    {
        return $this->hasMany(Reprogramacion::class, 'reserva_id');
    }

    /**
     * Última reprogramación: define el turno que la reserva ocupa hoy.
     */
    public function reprogramacionVigente(): HasOne
    {
        return $this->hasOne(Reprogramacion::class, 'reserva_id')->latestOfMany();
    }

    public function fueReprogramada(): bool
    {
        return $this->reprogramacionVigente !== null;
    }

    /**
     * Turno en el que el cliente juega hoy.
     *
     * Los campos `cancha_id` / `hora_inicio` / `hora_fin` de esta fila guardan
     * lo que se reservó y pagó originalmente, y no cambian al reprogramar.
     */
    public function canchaVigenteId(): int
    {
        return (int) ($this->reprogramacionVigente?->cancha_nueva_id ?? $this->cancha_id);
    }

    public function horaInicioVigente(): Carbon
    {
        return Carbon::parse($this->reprogramacionVigente?->hora_inicio_nueva ?? $this->hora_inicio);
    }

    public function horaFinVigente(): Carbon
    {
        return Carbon::parse($this->reprogramacionVigente?->hora_fin_nueva ?? $this->hora_fin);
    }

    public function canchaVigente(): ?Cancha
    {
        return $this->reprogramacionVigente?->canchaNueva ?? $this->cancha;
    }
}
