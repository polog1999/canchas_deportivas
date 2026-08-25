<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, HasSpanishTimestamps, Notifiable;

    protected static function newFactory(): UsuarioFactory
    {
        return UsuarioFactory::new();
    }

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'usuario',
        'clave',
        'nombre',
        'correo_electronico',
        'correo_verificado_en',
        'activo',
        'token_recordar',
        'secreto_dos_factores',
        'codigos_recuperacion_dos_factores',
        'dos_factores_confirmado_en',
    ];

    protected $hidden = [
        'clave',
        'token_recordar',
        'secreto_dos_factores',
        'codigos_recuperacion_dos_factores',
    ];

    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'dos_factores_confirmado_en' => 'datetime',
            'clave' => 'hashed',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->clave;
    }

    public function getAuthPasswordName(): string
    {
        return 'clave';
    }

    public function getRememberTokenName(): string
    {
        return 'token_recordar';
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->correo_electronico;
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->correo_electronico;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function perfil(): HasOne
    {
        return $this->hasOne(Perfil::class, 'usuario_id');
    }

    /** Alias de compatibilidad */
    public function profile(): HasOne
    {
        return $this->perfil();
    }

    public function menus(): Collection
    {
        if (! $this->rol) {
            return collect();
        }

        return $this->rol->menus()
            ->where('menus.activo', true)
            ->orderBy('menus.orden')
            ->get();
    }

    /**
     * Árbol de menús para el sidebar: raíces + hijos asignados.
     * Si un hijo está asignado, también se muestra su padre (carpeta).
     */
    public function menusArbol(): Collection
    {
        $asignados = $this->menus();
        if ($asignados->isEmpty()) {
            return collect();
        }

        $idsAsignados = $asignados->pluck('id');
        $idsPadre = $asignados->pluck('id_padre')->filter()->unique();
        $idsRaizVisibles = $asignados
            ->whereNull('id_padre')
            ->pluck('id')
            ->merge($idsPadre)
            ->unique()
            ->values();

        return Menu::query()
            ->with([
                'hijos' => fn ($q) => $q
                    ->whereIn('id', $idsAsignados)
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->orderBy('id'),
            ])
            ->whereIn('id', $idsRaizVisibles)
            ->whereNull('id_padre')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    public function tieneRol(string ...$nombres): bool
    {
        return $this->rol && in_array($this->rol->nombre, $nombres, true);
    }

    public function puede(string $permiso): bool
    {
        if ($this->tieneRol('admin', 'ADMIN', 'SUPERADMIN')) {
            return true;
        }

        // Solo la ruta/link del menú autoriza; el nombre es solo etiqueta visual.
        return $this->tieneMenuRuta($permiso);
    }

    public function tieneMenuRuta(string $ruta): bool
    {
        if ($this->tieneRol('admin', 'ADMIN', 'SUPERADMIN')) {
            return true;
        }

        $ruta = $this->normalizarRutaMenu($ruta);

        return $this->menus()->contains(
            fn (Menu $menu) => $this->normalizarRutaMenu($menu->ruta) === $ruta
        );
    }

    private function normalizarRutaMenu(string $ruta): string
    {
        $ruta = trim($ruta);

        if ($ruta === '') {
            return '';
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            $path = parse_url($ruta, PHP_URL_PATH) ?: '/';

            return '/' . ltrim($path, '/');
        }

        return '/' . ltrim($ruta, '/');
    }
}
