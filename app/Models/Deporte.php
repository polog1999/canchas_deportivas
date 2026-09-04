<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

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

    // Si ya es una URL externa o base64
    if (
        preg_match('#^(https?:)?//#i', $imagen) ||
        str_starts_with($imagen, 'data:')
    ) {
        return $imagen;
    }

    // Como en BD solo guardas el nombre:
    // deporte_1_abcd1234.jpg
    $filename = basename($imagen);

    $path = 'imagenes/deportes/' . $filename;

    // Si existe en storage, devolver su URL pública
    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->url($path);
    }

    // Si no existe, devolver fallback
    return $fallback
        ?? 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80';
}
}
