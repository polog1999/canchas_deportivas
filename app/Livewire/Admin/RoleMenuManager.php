<?php

namespace App\Livewire\Admin;

use App\Models\Menu;
use App\Models\Rol;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RoleMenuManager extends Component
{
    public $rolId = null;

    /** @var array<int, bool> */
    public $menuAcceso = [];

    public $isRoleModalOpen = false;
    public $nuevoRolNombre = '';
    public $nuevoRolDescripcion = '';

    public function mount(): void
    {
        $primerRol = Rol::orderBy('nombre')->first();
        $this->rolId = $primerRol?->id;
        $this->cargarPermisos();
    }

    public function updatedRolId(): void
    {
        $this->cargarPermisos();
    }

    public function cargarPermisos(): void
    {
        $this->menuAcceso = [];

        if (! $this->rolId) {
            return;
        }

        $rol = Rol::with('menus')->find($this->rolId);
        if (! $rol) {
            return;
        }

        $asignados = $rol->menus->pluck('id')->all();

        foreach (Menu::orderBy('orden')->pluck('id') as $menuId) {
            $this->menuAcceso[$menuId] = in_array($menuId, $asignados, true);
        }
    }

    public function guardarPermisos(): void
    {
        $rol = Rol::findOrFail($this->rolId);

        $ids = collect($this->menuAcceso)
            ->filter()
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $rol->menus()->sync($ids);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Permisos guardados',
            'text' => 'Los menús del rol se actualizaron correctamente.',
        ]);

        $this->cargarPermisos();
    }

    public function toggleRolActivo(): void
    {
        $rol = Rol::findOrFail($this->rolId);
        $rol->activo = ! $rol->activo;
        $rol->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $rol->activo ? 'Rol activado' : 'Rol desactivado',
            'text' => 'El estado del rol se actualizó correctamente.',
        ]);
    }

    public function openRoleModal(): void
    {
        $this->resetValidation();
        $this->nuevoRolNombre = '';
        $this->nuevoRolDescripcion = '';
        $this->isRoleModalOpen = true;
    }

    public function closeRoleModal(): void
    {
        $this->isRoleModalOpen = false;
        $this->resetValidation();
    }

    public function crearRol(): void
    {
        $this->validate([
            'nuevoRolNombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nombre'),
            ],
            'nuevoRolDescripcion' => 'nullable|string|max:255',
        ], [], [
            'nuevoRolNombre' => 'nombre del rol',
            'nuevoRolDescripcion' => 'descripción',
        ]);

        $rol = Rol::create([
            'nombre' => $this->nuevoRolNombre,
            'descripcion' => $this->nuevoRolDescripcion ?: null,
            'activo' => true,
        ]);

        $this->rolId = $rol->id;
        $this->cargarPermisos();
        $this->closeRoleModal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Rol creado',
            'text' => 'El nuevo rol está listo para asignar menús.',
        ]);
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        return view('livewire.admin.role-menu-manager', [
            'roles' => Rol::orderBy('nombre')->get(),
            'menus' => Menu::listaPlanaOrdenada(),
            'rolActual' => $this->rolId ? Rol::find($this->rolId) : null,
        ]);
    }
}
