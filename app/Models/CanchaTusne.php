<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanchaTusne extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'canchas_tusne';

    protected $fillable = [
        'cancha_id',
        'catalogo_tusne_id',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class, 'cancha_id');
    }

    public function catalogoTusne(): BelongsTo
    {
        return $this->belongsTo(CatalogoTusne::class, 'catalogo_tusne_id');
    }
}
