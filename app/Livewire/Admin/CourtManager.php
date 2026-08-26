<?php

namespace App\Livewire\Admin;

use App\Models\Cancha;
use App\Models\CanchaTusne;
use App\Models\CatalogoTusne;
use App\Models\Deporte;
use App\Models\Sede;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CourtManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedLocationFilter = '';
    public $selectedDeporteFilter = '';

    public $isOpen = false;
    public $isEditMode = false;
    public $courtId;

    public $catalogo_tusne_id;
    public $sede_id = '';
    public $nombre = '';
    /** @var array<int|string> */
    public $deporte_ids = [];
    public $precio_por_hora = 0;
    public $esta_activo = true;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedLocationFilter()
    {
        $this->resetPage();
    }

    public function updatingSelectedDeporteFilter()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'catalogo_tusne_id' => 'required|exists:catalogos_tusne,id',
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => 'required|string|max:255',
            'deporte_ids' => 'required|array|min:1',
            'deporte_ids.*' => 'integer|exists:deportes,id',
            'precio_por_hora' => 'nullable|numeric|min:0',
            'esta_activo' => 'required|boolean',
        ];
    }

    protected $messages = [
        'sede_id.required' => 'Debe seleccionar la sede a la que pertenece la cancha.',
        'sede_id.exists' => 'La sede seleccionada no es válida.',
        'nombre.required' => 'El nombre de la cancha es obligatorio.',
        'deporte_ids.required' => 'Debe seleccionar al menos un deporte.',
        'deporte_ids.min' => 'Debe seleccionar al menos un deporte.',
        'catalogo_tusne_id.required' => 'Debe seleccionar un código TUSNE.',
    ];

    #[Layout('components.app-layout')]
    public function render()
    {
        $tusnes = CatalogoTusne::where('esta_activo', true)->get();
        $locations = Sede::where('esta_activo', true)->orderBy('nombre', 'asc')->get();
        $deportes = Deporte::query()->orderBy('nombre')->get(['id', 'nombre']);

        $courts = Cancha::with(['sede', 'canchasTusne', 'deportes'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'ilike', '%' . $this->search . '%')
                        ->orWhereHas('sede', function ($locQuery) {
                            $locQuery->where('nombre', 'ilike', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->selectedLocationFilter, function ($query) {
                $query->where('sede_id', $this->selectedLocationFilter);
            })
            ->when($this->selectedDeporteFilter, function ($query) {
                $query->whereHas('deportes', fn ($d) => $d->where('deportes.id', $this->selectedDeporteFilter));
            })
            ->orderBy('sede_id', 'asc')
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.admin.court-manager', [
            'courts' => $courts,
            'locations' => $locations,
            'deportes' => $deportes,
            'tusnes' => $tusnes,
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
        $this->catalogo_tusne_id = null;
        $this->courtId = null;
        $this->isEditMode = false;
        $this->sede_id = '';
        $this->nombre = '';
        $this->deporte_ids = [];
        $this->precio_por_hora = 0;
        $this->esta_activo = true;
        $this->resetValidation();
    }

    public function saveCourt()
    {
        $validatedData = $this->validate();
        $catalogoId = $validatedData['catalogo_tusne_id'];
        $deporteIds = array_map('intval', $validatedData['deporte_ids']);
        unset($validatedData['catalogo_tusne_id'], $validatedData['deporte_ids']);

        $court = Cancha::updateOrCreate(
            ['id' => $this->courtId],
            $validatedData
        );

        $court->deportes()->sync($deporteIds);

        CanchaTusne::updateOrCreate(
            ['cancha_id' => $court->id],
            ['catalogo_tusne_id' => $catalogoId]
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Cancha Actualizada' : 'Cancha Registrada',
            'text' => 'La información del espacio deportivo ha sido guardada.',
        ]);

        $this->closeModal();
    }

    public function editCourt($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->courtId = $id;

        $court = Cancha::with(['canchasTusne', 'deportes'])->findOrFail($id);

        $this->sede_id = $court->sede_id;
        $this->nombre = $court->nombre;
        $this->deporte_ids = $court->deportes->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
        $this->precio_por_hora = $court->precio_por_hora;
        $this->esta_activo = (bool) $court->esta_activo;
        $this->catalogo_tusne_id = $court->canchasTusne->first()?->catalogo_tusne_id;
        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $court = Cancha::findOrFail($id);
        $court->esta_activo = ! $court->esta_activo;
        $court->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Modificado',
            'text' => 'El estado de la cancha ha sido actualizado.',
        ]);
    }
}
