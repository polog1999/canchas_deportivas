<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class ReservaFlow
{
    public static function esPortal(): bool
    {
        $nombre = Route::currentRouteName() ?? '';

        return str_starts_with($nombre, 'portal.reservar.');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public static function ruta(string $name, array $params = []): string
    {
        if (in_array($name, ['registrar', 'buscar-documento', 'verificar-acceso', 'ocupacion'], true)) {
            return route('reservar.'.$name, $params);
        }

        if (self::esPortal()) {
            if ($name === 'index') {
                return route('portal.reservar.index', $params);
            }

            return route('portal.reservar.'.$name, $params);
        }

        if ($name === 'index') {
            return url('/');
        }

        return route('reservar.'.$name, $params);
    }

    public static function marcarDesdePortal(): void
    {
        session(['reserva_desde_portal' => true]);
    }

    public static function desdePortalActivo(): bool
    {
        return (bool) session('reserva_desde_portal', false);
    }

    public static function limpiarMarcaPortal(): void
    {
        session()->forget('reserva_desde_portal');
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public static function rutaResultado(string $estado, array $params = []): string
    {
        $params = array_merge(['estado' => $estado], $params);

        if (self::desdePortalActivo()) {
            return route('portal.reservar.resultado', $params);
        }

        return route('reservar.resultado', $params);
    }
}
