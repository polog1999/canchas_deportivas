<?php

namespace App\Models;

use App\Enums\CourtType;
use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancha extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'canchas';

    protected $fillable = [
        'sede_id',
        'nombre',
        'tipo',
        'precio_por_hora',
        'esta_activo',
    ];

    protected function casts(): array
    {
        return [
            'esta_activo' => 'boolean',
            'tipo' => CourtType::class,
            'precio_por_hora' => 'decimal:2',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function deportes(): BelongsToMany
    {
        return $this->belongsToMany(Deporte::class, 'canchas_deportes', 'cancha_id', 'deporte_id');
    }

    public function canchasTusne(): HasMany
    {
        return $this->hasMany(CanchaTusne::class, 'cancha_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'cancha_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cancha_id');
    }
}
