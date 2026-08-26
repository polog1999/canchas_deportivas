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
    ];

    public function canchas(): BelongsToMany
    {
        return $this->belongsToMany(Cancha::class, 'canchas_deportes', 'deporte_id', 'cancha_id');
    }
}
