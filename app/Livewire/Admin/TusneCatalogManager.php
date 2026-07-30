<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TusneCatalog; // Asegúrese de crear este modelo o use su espacio de nombres
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

class TusneCatalogManager extends Component
{
    use WithPagination;

    // Propiedades de Búsqueda y Navegación
    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $tusneId;

    // Propiedades de Formulario (Mapeadas a la migración)
    public $tusne_group = '23';
    public $tusne_code = '';
    public $local_description = '';
    public $includes_dressing_rooms = false;
    public $includes_stands = false;
    public $includes_goals_f11 = false;
    public $has_gate_revenue = false;
    public $time_modifier = 'none'; // 'day', 'night', 'none'
    public $client_type = 'general'; // 'vecino', 'no_vecino', 'general'
    public $is_active = true;

    protected $listeners = ['deleteTusne' => 'delete'];
    // 3. Función de búsqueda en Oracle vinculada a la lupa
    public function searchOracleTusne()
    {
        if (empty($this->tusne_group) || empty($this->tusne_code)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Campos requeridos',
                'text' => 'Por favor, ingrese el Grupo y el Código de Servicio para buscar en Oracle.'
            ]);
            return;
        }

        try {
            // Instanciar el servicio con app() tal como se solicitó
            $oracleService = app(\App\Services\OracleService::class);
            $match = $oracleService->getTusnePorCodigo((string) $this->tusne_group, (string) $this->tusne_code);

            if ($match) {
                // Soportar las columnas de descripción comunes en Oracle de forma dinámica
                $description = $match->condescrip ?? null;

                if ($description) {
                    $this->local_description = trim($description);
                    $this->dispatch('swal', [
                        'icon' => 'success',
                        'title' => 'Concepto Encontrado',
                        'text' => 'Descripción autocompletada con éxito desde Oracle.'
                    ]);
                } else {
                    $this->dispatch('swal', [
                        'icon' => 'info',
                        'title' => 'Registro Encontrado',
                        'text' => 'Se encontró el registro, pero no se pudo leer la descripción de las columnas esperadas.'
                    ]);
                }
            } else {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'No Encontrado',
                    'text' => 'No se encontró ningún concepto con Grupo: ' . $this->tusne_group . ' y Código: ' . $this->tusne_code . ' en Oracle.'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Conexión',
                'text' => 'No se pudo completar la consulta en Oracle: ' . $e->getMessage()
            ]);
        }
    }
    // Reiniciar la paginación al buscar
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Reglas de Validación Dinámicas
    protected function rules()
    {
        return [
            'tusne_group' => 'required|string|max:2',
            'tusne_code' => [
                'required',
                'string',
                'max:5',
                // Validación de clave única compuesta (tusne_group + tusne_code)
                Rule::unique('tusne_catalogs')
                    ->where('tusne_group', $this->tusne_group)
                    ->ignore($this->tusneId)
            ],
            'local_description' => 'required|string|max:255',
            'includes_dressing_rooms' => 'boolean',
            'includes_stands' => 'boolean',
            'includes_goals_f11' => 'boolean',
            'has_gate_revenue' => 'boolean',
            'time_modifier' => 'required|in:day,night,none',
            'client_type' => 'required|in:vecino,no_vecino,general',
            'is_active' => 'required|boolean',
        ];
    }

    protected $messages = [
        'tusne_group.required' => 'El código de grupo de Oracle es obligatorio.',
        'tusne_code.required' => 'El código del servicio es obligatorio.',
        'tusne_code.unique' => 'Ya existe un registro con esta combinación de Grupo y Código de Servicio.',
        'local_description.required' => 'La descripción local para la web es obligatoria.',
    ];
    
    #[Layout('components.app-layout')]
    public function render()
    {
        $catalogs = TusneCatalog::query()
            ->where(function ($query) {
                $query->where('local_description', 'ilike', '%' . $this->search . '%')
                    ->orWhere('tusne_group', 'ilike', '%' . $this->search . '%')
                    ->orWhere('tusne_code', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('tusne_group', 'asc')
            ->orderBy('tusne_code', 'asc')
            ->paginate(5);

        return view('livewire.admin.tusne-catalog-manager', [
            'catalogs' => $catalogs
        ]);
    }

    // Acciones de Modal
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
        $this->tusneId = null;
        $this->isEditMode = false;
        // $this->tusne_group = '';
        $this->tusne_code = '';
        $this->local_description = '';
        $this->includes_dressing_rooms = false;
        $this->includes_stands = false;
        $this->includes_goals_f11 = false;
        $this->has_gate_revenue = false;
        $this->time_modifier = 'none';
        $this->client_type = 'general';
        $this->is_active = true;
        $this->resetValidation();
    }

    // Guardar / Actualizar Registro
    public function saveTusne()
    {
        $validatedData = $this->validate();

        TusneCatalog::updateOrCreate(
            ['id' => $this->tusneId],
            $validatedData
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Registro Actualizado' : 'Registro Guardado',
            'text' => 'El catálogo TUSNE se ha procesado correctamente en el sistema.'
        ]);

        $this->closeModal();
    }

    // Cargar datos para edición
    public function editTusne($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->tusneId = $id;

        $catalog = TusneCatalog::findOrFail($id);

        $this->tusne_group = $catalog->tusne_group;
        $this->tusne_code = $catalog->tusne_code;
        $this->local_description = $catalog->local_description;
        $this->includes_dressing_rooms = (bool)$catalog->includes_dressing_rooms;
        $this->includes_stands = (bool)$catalog->includes_stands;
        $this->includes_goals_f11 = (bool)$catalog->includes_goals_f11;
        $this->has_gate_revenue = (bool)$catalog->has_gate_revenue;
        $this->time_modifier = $catalog->time_modifier;
        $this->client_type = $catalog->client_type;
        $this->is_active = (bool)$catalog->is_active;

        $this->isOpen = true;
    }

    // Alternar Estado lógico activo/inactivo de forma directa
    public function toggleStatus($id)
    {
        $catalog = TusneCatalog::findOrFail($id);
        $catalog->is_active = !$catalog->is_active;
        $catalog->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Actualizado',
            'text' => 'El estado del código TUSNE ha sido modificado con éxito.'
        ]);
    }
}
