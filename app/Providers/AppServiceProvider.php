<?php

namespace App\Providers;

use App\Mail\CustomMailManager;
use App\Models\Usuario;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('mail.manager', function ($app) {
            return new CustomMailManager($app);
        });
    }

    public function boot(): void
    {
        $this->app->make(MailConfigService::class)->aplicarDesdeEnv();

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
