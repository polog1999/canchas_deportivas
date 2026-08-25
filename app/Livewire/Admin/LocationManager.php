<?php

namespace App\Livewire\Admin;

use App\Models\Sede;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithPagination;

    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $locationId;

    public $nombre = '';
    public $direccion = '';
    public $enlace_mapas = '';
    public $esta_activo = true;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'enlace_mapas' => 'required|string|max:500',
            'esta_activo' => 'required|boolean',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la sede es obligatorio.',
        'direccion.required' => 'La dirección física es obligatoria.',
        'enlace_mapas.required' => 'El enlace de Google Maps es obligatorio.',
    ];

    #[Layout('components.app-layout')]
    public function render()
    {
        $locations = Sede::query()
            ->where(function ($query) {
                $query->where('nombre', 'ilike', '%' . $this->search . '%')
                    ->orWhere('direccion', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.admin.location-manager', [
            'locations' => $locations,
        ]);
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->locationId = null;
        $this->isEditMode = false;
        $this->nombre = '';
        $this->direccion = '';
        $this->enlace_mapas = '';
        $this->esta_activo = true;
        $this->resetValidation();
    }

    public function saveLocation()
    {
        $validatedData = $this->validate();

        Sede::updateOrCreate(
            ['id' => $this->locationId],
            $validatedData
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Sede Actualizada' : 'Sede Registrada',
            'text' => 'La información de la sede se ha guardado correctamente.',
        ]);

        $this->closeModal();
    }

    public function editLocation($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->locationId = $id;

        $location = Sede::findOrFail($id);

        $this->nombre = $location->nombre;
        $this->direccion = $location->direccion;
        $this->enlace_mapas = $location->enlace_mapas;
        $this->esta_activo = (bool) $location->esta_activo;

        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $location = Sede::findOrFail($id);
        $location->esta_activo = ! $location->esta_activo;
        $location->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Modificado',
            'text' => 'El estado de la sede ha sido actualizado con éxito.',
        ]);
    }
}
