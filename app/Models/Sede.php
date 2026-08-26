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
        'hora_inicio',
        'hora_fin',
        'imagen',
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

    /** URL usable: en BD solo el nombre (ej. sede1.jpg) → public/imagenes/sedes/sede1.jpg */
    public function urlImagen(?string $fallback = null): string
    {
        $imagen = trim((string) $this->imagen);

        if ($imagen === '') {
            return $fallback
                ?? 'https://images.unsplash.com/photo-1459865266369-566976b10f9e?auto=format&fit=crop&w=800&q=80';
        }

        if (preg_match('#^(https?:)?//#i', $imagen) || str_starts_with($imagen, 'data:')) {
            return $imagen;
        }

        // Ya viene con ruta relativa (imagenes/sedes/... o /imagenes/sedes/...)
        if (str_contains($imagen, '/')) {
            return asset(ltrim($imagen, '/'));
        }

        // Solo nombre de archivo en BD
        return asset('imagenes/sedes/' . $imagen);
    }
}
