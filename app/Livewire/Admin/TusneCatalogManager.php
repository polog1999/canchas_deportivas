<?php

namespace App\Livewire\Admin;

use App\Models\CatalogoTusne;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TusneCatalogManager extends Component
{
    use WithPagination;

    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $tusneId;

    public $grupo_tusne = '23';
    public $codigo_tusne = '';
    public $descripcion_local = '';
    public $incluye_camerinos = false;
    public $incluye_tribunas = false;
    public $incluye_arcos_f11 = false;
    public $tiene_recaudacion_taquilla = false;
    public $modificador_tiempo = 'ninguno';
    public $tipo_cliente = 'general';
    public $esta_activo = true;

    protected $listeners = ['deleteTusne' => 'delete'];

    public function searchOracleTusne()
    {
        if (empty($this->grupo_tusne) || empty($this->codigo_tusne)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Campos requeridos',
                'text' => 'Por favor, ingrese el Grupo y el Código de Servicio para buscar en Oracle.',
            ]);

            return;
        }

        try {
            $oracleService = app(\App\Services\OracleService::class);
            $match = $oracleService->getTusnePorCodigo((string) $this->grupo_tusne, (string) $this->codigo_tusne);

            if ($match) {
                $description = $match->condescrip ?? null;

                if ($description) {
                    $this->descripcion_local = trim($description);
                    $this->dispatch('swal', [
                        'icon' => 'success',
                        'title' => 'Concepto Encontrado',
                        'text' => 'Descripción autocompletada con éxito desde Oracle.',
                    ]);
                } else {
                    $this->dispatch('swal', [
                        'icon' => 'info',
                        'title' => 'Registro Encontrado',
                        'text' => 'Se encontró el registro, pero no se pudo leer la descripción de las columnas esperadas.',
                    ]);
                }
            } else {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'No Encontrado',
                    'text' => 'No se encontró ningún concepto con Grupo: ' . $this->grupo_tusne . ' y Código: ' . $this->codigo_tusne . ' en Oracle.',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Conexión',
                'text' => 'No se pudo completar la consulta en Oracle: ' . $e->getMessage(),
            ]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'grupo_tusne' => 'required|string|max:2',
            'codigo_tusne' => [
                'required',
                'string',
                'max:5',
                Rule::unique('catalogos_tusne')
                    ->where('grupo_tusne', $this->grupo_tusne)
                    ->ignore($this->tusneId),
            ],
            'descripcion_local' => 'required|string|max:255',
            'incluye_camerinos' => 'boolean',
            'incluye_tribunas' => 'boolean',
            'incluye_arcos_f11' => 'boolean',
            'tiene_recaudacion_taquilla' => 'boolean',
            'modificador_tiempo' => 'required|in:dia,noche,ninguno',
            'tipo_cliente' => 'required|in:vecino,no_vecino,general',
            'esta_activo' => 'required|boolean',
        ];
    }

    protected $messages = [
        'grupo_tusne.required' => 'El código de grupo de Oracle es obligatorio.',
        'codigo_tusne.required' => 'El código del servicio es obligatorio.',
        'codigo_tusne.unique' => 'Ya existe un registro con esta combinación de Grupo y Código de Servicio.',
        'descripcion_local.required' => 'La descripción local para la web es obligatoria.',
    ];

    #[Layout('components.app-layout')]
    public function render()
    {
        $catalogs = CatalogoTusne::query()
            ->where(function ($query) {
                $query->where('descripcion_local', 'ilike', '%' . $this->search . '%')
                    ->orWhere('grupo_tusne', 'ilike', '%' . $this->search . '%')
                    ->orWhere('codigo_tusne', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('grupo_tusne', 'asc')
            ->orderBy('codigo_tusne', 'asc')
            ->paginate(5);

        return view('livewire.admin.tusne-catalog-manager', [
            'catalogs' => $catalogs,
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
        $this->tusneId = null;
        $this->isEditMode = false;
        $this->codigo_tusne = '';
        $this->descripcion_local = '';
        $this->incluye_camerinos = false;
        $this->incluye_tribunas = false;
        $this->incluye_arcos_f11 = false;
        $this->tiene_recaudacion_taquilla = false;
        $this->modificador_tiempo = 'ninguno';
        $this->tipo_cliente = 'general';
        $this->esta_activo = true;
        $this->resetValidation();
    }

    public function saveTusne()
    {
        $validatedData = $this->validate();

        CatalogoTusne::updateOrCreate(
            ['id' => $this->tusneId],
            $validatedData
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Registro Actualizado' : 'Registro Guardado',
            'text' => 'El catálogo TUSNE se ha procesado correctamente en el sistema.',
        ]);

        $this->closeModal();
    }

    public function editTusne($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->tusneId = $id;

        $catalog = CatalogoTusne::findOrFail($id);

        $this->grupo_tusne = $catalog->grupo_tusne;
        $this->codigo_tusne = $catalog->codigo_tusne;
        $this->descripcion_local = $catalog->descripcion_local;
        $this->incluye_camerinos = (bool) $catalog->incluye_camerinos;
        $this->incluye_tribunas = (bool) $catalog->incluye_tribunas;
        $this->incluye_arcos_f11 = (bool) $catalog->incluye_arcos_f11;
        $this->tiene_recaudacion_taquilla = (bool) $catalog->tiene_recaudacion_taquilla;
        $this->modificador_tiempo = $catalog->modificador_tiempo;
        $this->tipo_cliente = $catalog->tipo_cliente;
        $this->esta_activo = (bool) $catalog->esta_activo;

        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $catalog = CatalogoTusne::findOrFail($id);
        $catalog->esta_activo = ! $catalog->esta_activo;
        $catalog->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Actualizado',
            'text' => 'El estado del código TUSNE ha sido modificado con éxito.',
        ]);
    }
}
