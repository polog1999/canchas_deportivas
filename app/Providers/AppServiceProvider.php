<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (?Usuario $user, string $ability) {
            if (! $user) {
                return null;
            }

            if ($user->tieneRol('admin', 'ADMIN', 'SUPERADMIN')) {
                return true;
            }

            return $user->puede($ability) ?: null;
        });
    }
}
