<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reprogramacion extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'reprogramaciones';

    protected $fillable = [
        'reserva_id',
        'cancha_anterior_id',
        'cancha_nueva_id',
        'hora_inicio_anterior',
        'hora_fin_anterior',
        'hora_inicio_nueva',
        'hora_fin_nueva',
        'monto_validado',
        'catalogo_tusne_id',
        'motivo',
        'autorizado_por',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio_anterior' => 'datetime',
            'hora_fin_anterior' => 'datetime',
            'hora_inicio_nueva' => 'datetime',
            'hora_fin_nueva' => 'datetime',
            'monto_validado' => 'decimal:2',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /**
     * Solo la última reprogramación de cada reserva: es la que define el turno
     * que la reserva ocupa hoy. Las anteriores quedan como historial.
     */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('max(id)')
                ->from($this->getTable())
                ->groupBy('reserva_id');
        });
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function canchaAnterior(): BelongsTo
    {
        return $this->belongsTo(Cancha::class, 'cancha_anterior_id');
    }

    public function canchaNueva(): BelongsTo
    {
        return $this->belongsTo(Cancha::class, 'cancha_nueva_id');
    }

    public function catalogoTusne(): BelongsTo
    {
        return $this->belongsTo(CatalogoTusne::class, 'catalogo_tusne_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }
}
