<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'sliders';

    protected $fillable = [
        'titulo',
        'texto_boton',
        'enlace_boton',
        'imagen',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /** URL: en BD solo el archivo → public/imagenes/slider/{archivo} */
    public function urlImagen(?string $fallback = null): string
    {
        $imagen = trim((string) $this->imagen);

        if ($imagen === '') {
            return $fallback
                ?? 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1600&q=80';
        }

        if (preg_match('#^(https?:)?//#i', $imagen) || str_starts_with($imagen, 'data:')) {
            return $imagen;
        }

        if (str_contains($imagen, '/')) {
            return asset(ltrim($imagen, '/'));
        }

        return asset('storage/imagenes/slider/' . $imagen);
    }
}
