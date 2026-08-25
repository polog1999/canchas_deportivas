<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;
use Livewire\Attributes\Layout;

class LocationManager extends Component
{
    use WithPagination;

    // Propiedades de Filtro y Navegación
    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $locationId;

    // Propiedades del Formulario
    public $name = '';
    public $address = '';
    public $link_maps = '';
    public $is_active = true;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'link_maps' => 'required|string|max:500',
            'is_active' => 'required|boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre de la sede es obligatorio.',
        'address.required' => 'La dirección física es obligatoria.',
        'link_maps.required' => 'El enlace de Google Maps es obligatorio.',
    ];
    #[Layout('components.app-layout')]
    public function render()
    {
        $locations = Location::query()
            ->where(function ($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('address', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('livewire.admin.location-manager', [
            'locations' => $locations
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
        $this->name = '';
        $this->address = '';
        $this->link_maps = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function saveLocation()
    {
        $validatedData = $this->validate();

        Location::updateOrCreate(
            ['id' => $this->locationId],
            $validatedData
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Sede Actualizada' : 'Sede Registrada',
            'text' => 'La información de la sede se ha guardado correctamente.'
        ]);

        $this->closeModal();
    }

    public function editLocation($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->locationId = $id;

        $location = Location::findOrFail($id);

        $this->name = $location->name;
        $this->address = $location->address;
        $this->link_maps = $location->link_maps;
        $this->is_active = (bool)$location->is_active;

        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $location = Location::findOrFail($id);
        $location->is_active = !$location->is_active;
        $location->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Modificado',
            'text' => 'El estado de la sede ha sido actualizado con éxito.'
        ]);
    }
}