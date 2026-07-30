<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TusneCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tusne_group',
        'tusne_code',
        'local_description',
        'includes_dressing_rooms',
        'includes_stands',
        'includes_goals_f11',
        'has_gate_revenue',
        'time_modifier',
        'client_type',
        'is_active',
    ];

    // Castings de datos para interactuar correctamente con BOOLEANS en PostgreSQL
    protected $casts = [
        'includes_dressing_rooms' => 'boolean',
        'includes_stands' => 'boolean',
        'includes_goals_f11' => 'boolean',
        'has_gate_revenue' => 'boolean',
        'is_active' => 'boolean',
    ];
}
