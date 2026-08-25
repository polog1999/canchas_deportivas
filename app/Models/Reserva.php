<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
            'precio_total' => 'decimal:2',
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
}
