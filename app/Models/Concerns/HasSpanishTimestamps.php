<?php

namespace App\Models\Concerns;

trait HasSpanishTimestamps
{
    public function getCreatedAtColumn(): string
    {
        return 'creado_en';
    }

    public function getUpdatedAtColumn(): string
    {
        return 'actualizado_en';
    }
}
