<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            abort(403);
        }

        $lista = collect($permisos)
            ->flatMap(fn (string $permiso) => explode('|', $permiso))
            ->map(fn (string $permiso) => trim($permiso))
            ->filter()
            ->values();

        foreach ($lista as $permiso) {
            if ($usuario->puede($permiso) || $usuario->tieneMenuRuta($permiso)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
