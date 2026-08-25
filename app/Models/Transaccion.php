<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaccion extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'transacciones';

    protected $fillable = [
        'reserva_id',
        'transaccion_id',
        'codigo_autorizacion',
        'marca_tarjeta',
        'tarjeta_enmascarada',
        'monto',
        'estado',
        'respuesta_bruta',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'respuesta_bruta' => 'array',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'transaccion_id');
    }
}
