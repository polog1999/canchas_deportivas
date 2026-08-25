<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'direccion',
        'enlace_mapas',
        'esta_activo',
    ];

    protected function casts(): array
    {
        return [
            'esta_activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function canchas(): HasMany
    {
        return $this->hasMany(Cancha::class, 'sede_id');
    }
}
