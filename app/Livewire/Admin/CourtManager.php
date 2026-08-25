<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Court;
use App\Models\Location;
use App\Enums\CourtType;
use App\Models\TusneCatalog;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;

class CourtManager extends Component
{
    use WithPagination;

    // Filtros de Búsqueda
    public $search = '';
    public $selectedLocationFilter = '';
    public $selectedTypeFilter = '';

    // Estado del Modal
    public $isOpen = false;
    public $isEditMode = false;
    public $courtId;

    // Campos del Formulario
    public $tusne_catalog_id;
    public $location_id = '';
    public $name = '';
    public $type = '';
    public $is_active = true;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedLocationFilter() { $this->resetPage(); }
    public function updatingSelectedTypeFilter() { $this->resetPage(); }

    protected function rules()
    {
        return [
            'tusne_catalog_id' => 'required|exists:tusne_catalogs',
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'type' => ['required', new Enum(CourtType::class)],
            'is_active' => 'required|boolean',
        ];
    }

    protected $messages = [
        'location_id.required' => 'Debe seleccionar la sede a la que pertenece la cancha.',
        'location_id.exists' => 'La sede seleccionada no es válida.',
        'name.required' => 'El nombre de la cancha es obligatorio.',
        'type.required' => 'Debe seleccionar el tipo de deporte/disciplina.',
    ];
    
 #[Layout('components.app-layout')]
    public function render()
    {
        $tusnes = TusneCatalog::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->orderBy('name', 'asc')->get();

        $courts = Court::with('location')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('location', function ($locQuery) {
                          $locQuery->where('name', 'ilike', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->selectedLocationFilter, function ($query) {
                $query->where('location_id', $this->selectedLocationFilter);
            })
            ->when($this->selectedTypeFilter, function ($query) {
                $query->where('type', $this->selectedTypeFilter);
            })
            ->orderBy('location_id', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('livewire.admin.court-manager', [
            'courts' => $courts,
            'locations' => $locations,
            'courtTypes' => CourtType::cases(),
            'tusnes' => $tusnes
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
        $this->tusne_catalog_id = null;
        $this->courtId = null;
        $this->isEditMode = false;
        $this->location_id = '';
        $this->name = '';
        $this->type = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function saveCourt()
    {
        $validatedData = $this->validate();

        Court::updateOrCreate(
            ['id' => $this->courtId],
            $validatedData
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Cancha Actualizada' : 'Cancha Registrada',
            'text' => 'La información del espacio deportivo ha sido guardada.'
        ]);

        $this->closeModal();
    }

    public function editCourt($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->courtId = $id;
        

        $court = Court::findOrFail($id);
        $tusne = $court->curtTusnes->

        $this->location_id = $court->location_id;
        $this->name = $court->name;
        $this->type = $court->type->value ?? $court->type;
        $this->is_active = (bool)$court->is_active;
        $this->tusne_catalog_id = 
        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $court = Court::findOrFail($id);
        $court->is_active = !$court->is_active;
        $court->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Modificado',
            'text' => 'El estado de la cancha ha sido actualizado.'
        ]);
    }
}