<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsoTusne extends Model
{
    protected $table = 'tipos_uso_tusne';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'orden',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];
}