<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Menu extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'menus';

    protected $fillable = [
        'id_padre',
        'nombre',
        'ruta',
        'icono',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
            'id_padre' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_padre');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'id_padre')->orderBy('orden')->orderBy('id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'menus_roles', 'menu_id', 'rol_id')
            ->withPivot('id');
    }

    public function esRaiz(): bool
    {
        return blank($this->id_padre);
    }

    public function esEnlace(): bool
    {
        $ruta = trim((string) $this->ruta);

        return $ruta !== '' && ! in_array($ruta, ['#', '---', '-'], true);
    }

    /**
     * URL del menú. `ruta` puede ser un path libre (/portal/users)
     * o, por compatibilidad, un nombre de ruta Laravel (users).
     */
    public function url(): string
    {
        if (! $this->esEnlace()) {
            return '#';
        }

        $ruta = trim((string) $this->ruta);

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        if (str_starts_with($ruta, '/')) {
            return url($ruta);
        }

        if (\Illuminate\Support\Facades\Route::has($ruta)) {
            return route($ruta);
        }

        return url('/' . ltrim($ruta, '/'));
    }

    public function estaActivo(): bool
    {
        if (! $this->esEnlace()) {
            return $this->hijos->contains(fn (self $hijo) => $hijo->estaActivo());
        }

        $path = parse_url($this->url(), PHP_URL_PATH) ?: '/';
        $path = ltrim($path, '/');

        return request()->is($path) || request()->is($path.'/*');
    }

    /**
     * Lista plana en orden jerárquico (padres e hijos intercalados).
     */
    public static function listaPlanaOrdenada(): Collection
    {
        $raices = static::query()
            ->with(['hijos' => fn ($q) => $q->orderBy('orden')->orderBy('id')])
            ->whereNull('id_padre')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $lista = collect();

        foreach ($raices as $raiz) {
            $lista->push($raiz);
            foreach ($raiz->hijos as $hijo) {
                $lista->push($hijo);
            }
        }

        // Huérfanos (padre inexistente) al final
        $idsIncluidos = $lista->pluck('id');
        $huerfanos = static::query()
            ->whereNotNull('id_padre')
            ->whereNotIn('id', $idsIncluidos)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return $lista->concat($huerfanos);
    }
}
