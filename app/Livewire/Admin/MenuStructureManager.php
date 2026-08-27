<?php

namespace App\Livewire\Admin;

use App\Models\Menu;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MenuStructureManager extends Component
{
    public $isOpen = false;
    public $isEditMode = false;
    public $menuId = null;

    public $id_padre = null;
    public $nombre = '';
    public $ruta = '';
    public $icono = 'fa-circle';
    public $orden = 1;
    public $activo = true;

    protected function rules(): array
    {
        return [
            'id_padre' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')->where(fn ($q) => $q->whereNull('id_padre')),
                Rule::notIn(array_filter([$this->menuId])),
            ],
            'nombre' => 'required|string|max:100',
            'ruta' => 'required|string|max:200',
            'icono' => 'nullable|string|max:50',
            'orden' => 'required|integer|min:0',
            'activo' => 'required|boolean',
        ];
    }

    protected $validationAttributes = [
        'id_padre' => 'menú padre',
        'nombre' => 'nombre',
        'ruta' => 'ruta',
        'icono' => 'icono',
        'orden' => 'orden',
    ];

    public function openModal(?int $padreId = null): void
    {
        $this->resetForm();
        $this->id_padre = $padreId;
        $this->ruta = $padreId ? '' : '#';
        $this->orden = (int) Menu::when(
            $padreId,
            fn ($q) => $q->where('id_padre', $padreId),
            fn ($q) => $q->whereNull('id_padre')
        )->max('orden') + 1;
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->menuId = null;
        $this->isEditMode = false;
        $this->id_padre = null;
        $this->nombre = '';
        $this->ruta = '';
        $this->icono = 'fa-circle';
        $this->orden = 1;
        $this->activo = true;
        $this->resetValidation();
    }

    public function saveMenu(): void
    {
        $this->id_padre = $this->id_padre === '' || $this->id_padre === null
            ? null
            : (int) $this->id_padre;

        $data = $this->validate();
        $data['icono'] = $data['icono'] ?: 'fa-circle';
        $data['ruta'] = trim($data['ruta']) ?: '#';
        $data['id_padre'] = $data['id_padre'] ?: null;

        // Un menú con hijos no puede convertirse en submenú.
        if ($this->isEditMode && $data['id_padre']) {
            $tieneHijos = Menu::where('id_padre', $this->menuId)->exists();
            if ($tieneHijos) {
                $this->addError('id_padre', 'Este menú tiene submenús; no puede colgarse de otro.');

                return;
            }
        }

        Menu::updateOrCreate(
            ['id' => $this->menuId],
            $data
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Menú actualizado' : 'Menú creado',
            'text' => 'La estructura del menú se guardó correctamente.',
        ]);

        $this->closeModal();
    }

    public function editMenu(int $id): void
    {
        $this->resetForm();
        $menu = Menu::findOrFail($id);

        $this->isEditMode = true;
        $this->menuId = $menu->id;
        $this->id_padre = $menu->id_padre;
        $this->nombre = $menu->nombre;
        $this->ruta = $menu->ruta;
        $this->icono = $menu->icono ?: 'fa-circle';
        $this->orden = $menu->orden;
        $this->activo = (bool) $menu->activo;
        $this->isOpen = true;
    }

    public function toggleStatus(int $id): void
    {
        $menu = Menu::findOrFail($id);
        $menu->activo = ! $menu->activo;
        $menu->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => 'El menú se ' . ($menu->activo ? 'activó' : 'desactivó') . ' correctamente.',
        ]);
    }

    /**
     * @param  array<int, array{id:int, id_padre:?int, orden:int}>  $items
     */
    public function reordenarMenus(array $items): void
    {
        if ($items === []) {
            return;
        }

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $idPadre = isset($item['id_padre']) && $item['id_padre'] !== '' && $item['id_padre'] !== null
                ? (int) $item['id_padre']
                : null;
            $orden = (int) ($item['orden'] ?? 0);

            if ($id <= 0 || $orden <= 0) {
                continue;
            }

            if (! Menu::whereKey($id)->exists()) {
                continue;
            }

            if ($idPadre === $id) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Movimiento inválido',
                    'text' => 'Un menú no puede ser padre de sí mismo.',
                ]);

                return;
            }

            if ($idPadre && Menu::where('id_padre', $id)->exists()) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Movimiento inválido',
                    'text' => 'Un menú con submenús solo puede estar en el nivel raíz.',
                ]);

                return;
            }

            if ($idPadre) {
                $padre = Menu::find($idPadre);
                if (! $padre || $padre->id_padre !== null) {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Movimiento inválido',
                        'text' => 'Los submenús solo pueden colgarse de un menú raíz.',
                    ]);

                    return;
                }
            }
        }

        foreach ($items as $item) {
            $id = (int) $item['id'];
            $idPadre = isset($item['id_padre']) && $item['id_padre'] !== '' && $item['id_padre'] !== null
                ? (int) $item['id_padre']
                : null;

            Menu::whereKey($id)->update([
                'id_padre' => $idPadre,
                'orden' => (int) $item['orden'],
            ]);
        }

        $this->dispatch('menus-reordenados');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Orden guardado',
            'text' => 'La estructura de menús se actualizó.',
            'timer' => 1400,
            'showConfirmButton' => false,
        ]);
    }

    public function deleteMenu(int $id): void
    {
        $menu = Menu::findOrFail($id);

        if ($menu->hijos()->exists()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'No se puede eliminar',
                'text' => 'Primero elimina o mueve los submenús.',
            ]);

            return;
        }

        $menu->roles()->detach();
        $menu->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Menú eliminado',
            'text' => 'El menú se eliminó de la estructura.',
        ]);
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        $raices = Menu::query()
            ->with(['hijos' => fn ($q) => $q->orderBy('orden')->orderBy('id')])
            ->whereNull('id_padre')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('livewire.admin.menu-structure-manager', [
            'raices' => $raices,
            'padresDisponibles' => Menu::query()
                ->whereNull('id_padre')
                ->when($this->menuId, fn ($q) => $q->where('id', '!=', $this->menuId))
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
