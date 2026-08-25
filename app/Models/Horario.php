<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'horarios';

    protected $fillable = [
        'cancha_id',
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class, 'cancha_id');
    }
}
