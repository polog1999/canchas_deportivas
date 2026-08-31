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

        if (str_contains($imagen, '/')) {
            return asset(ltrim($imagen, '/'));
        }

        if (is_file(public_path('imagenes/sedes/' . $imagen))) {
            return asset('imagenes/sedes/' . $imagen);
        }

        if (is_file(public_path('sedes/' . $imagen))) {
            return asset('sedes/' . $imagen);
        }

        return asset('imagenes/sedes/' . $imagen);
    }

    /**
     * URL embebible para iframe a partir de enlace_mapas (coords @lat,lng o búsqueda).
     */
    public function urlMapaEmbed(): ?string
    {
        $enlace = trim((string) $this->enlace_mapas);
        if ($enlace === '') {
            return null;
        }

        if (str_contains($enlace, '/maps/embed')) {
            return $enlace;
        }

        if (preg_match('/@(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $enlace, $m)) {
            return 'https://maps.google.com/maps?q='.$m[1].','.$m[2].'&z=16&output=embed';
        }

        if (preg_match('/[?&]q=([^&]+)/', $enlace, $m)) {
            return 'https://maps.google.com/maps?q='.$m[1].'&z=15&output=embed';
        }

        $consulta = trim((string) ($this->direccion ?: $this->nombre));
        if ($consulta === '') {
            return null;
        }

        return 'https://maps.google.com/maps?q='.rawurlencode($consulta.' La Molina Lima Perú').'&z=15&output=embed';
    }
}
