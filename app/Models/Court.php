<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CourtType;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'type' => CourtType::class, // Casteo al Enum de PHP
    ];

    // Relación: Cada cancha pertenece a una Sede
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    } 

    public function curtTusnes(): HasMany
    {
        return $this->hasMany(CurtTusne::class);
    }
}