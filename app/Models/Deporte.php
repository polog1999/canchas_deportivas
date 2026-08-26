<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deporte extends Model
{
    protected $table = 'deportes';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'imagen',
    ];

    public function canchas(): BelongsToMany
    {
        return $this->belongsToMany(Cancha::class, 'canchas_deportes', 'deporte_id', 'cancha_id');
    }

    /** URL: en BD solo el nombre → public/imagenes/deportes/{archivo} */
    public function urlImagen(?string $fallback = null): string
    {
        $imagen = trim((string) $this->imagen);

        if ($imagen === '') {
            return $fallback
                ?? 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80';
        }

        if (preg_match('#^(https?:)?//#i', $imagen) || str_starts_with($imagen, 'data:')) {
            return $imagen;
        }

        // Path relativo guardado completo (ej. imagenes/deportes/futbol.jpg)
        if (str_contains($imagen, '/')) {
            $rel = ltrim($imagen, '/');
            if (is_file(public_path($rel))) {
                return asset($rel);
            }
        }

        $filename = basename($imagen);
        if (is_file(public_path('imagenes/deportes/' . $filename))) {
            return asset('imagenes/deportes/' . $filename);
        }

        // Si el nombre existe en BD pero el archivo aún no está, devolver la URL esperada
        return asset('imagenes/deportes/' . $filename);
    }
}
