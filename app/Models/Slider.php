<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

   
    public function urlImagen(?string $fallback = null): string
    {
        $imagen = trim((string) $this->imagen);

        if ($imagen === '') {
            return $fallback
                ?? 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1600&q=80';
        }

        if (
            preg_match('#^(https?:)?//#i', $imagen) ||
            str_starts_with($imagen, 'data:')
        ) {
            return $imagen;
        }

        $filename = basename($imagen);

        $path = 'imagenes/slider/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return $fallback
            ?? 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1600&q=80';
    }
}
