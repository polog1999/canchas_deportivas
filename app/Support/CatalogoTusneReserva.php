<?php

namespace App\Support;

use App\Models\CatalogoTusne;
use App\Models\Cancha;

class CatalogoTusneReserva
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function idDesdeDatos(array $data, Cancha $cancha): ?int
    {
        $tusneId = (int) ($data['tusne_id'] ?? 0);

        if ($tusneId <= 0) {
            return null;
        }

        $valido = CatalogoTusne::query()
            ->whereKey($tusneId)
            ->where('esta_activo', true)
            ->whereHas('canchas', fn ($q) => $q->where('canchas.id', $cancha->id))
            ->exists();

        return $valido ? $tusneId : null;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public static function idDesdeMeta(?array $meta): ?int
    {
        $tusneId = (int) ($meta['tusne_id'] ?? 0);

        return $tusneId > 0 ? $tusneId : null;
    }
}
