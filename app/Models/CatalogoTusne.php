<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoTusne extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'catalogos_tusne';

    protected $fillable = [
        'grupo_tusne',
        'codigo_tusne',
        'descripcion_local',
        'incluye_camerinos',
        'incluye_tribunas',
        'incluye_arcos_f11',
        'tiene_recaudacion_taquilla',
        'modificador_tiempo',
        'tipo_cliente',
        'esta_activo',
    ];

    protected function casts(): array
    {
        return [
            'incluye_camerinos' => 'boolean',
            'incluye_tribunas' => 'boolean',
            'incluye_arcos_f11' => 'boolean',
            'tiene_recaudacion_taquilla' => 'boolean',
            'esta_activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function canchasTusne(): HasMany
    {
        return $this->hasMany(CanchaTusne::class, 'catalogo_tusne_id');
    }

    public function canchas(): BelongsToMany
    {
        return $this->belongsToMany(Cancha::class, 'canchas_tusne', 'catalogo_tusne_id', 'cancha_id');
    }
}
