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

    // Campos del formulario
    public $grupo_tusne = '23';
    public $codigo_tusne = '';
    public $descripcion_local = '';
    
    // Clasificaciones de discriminación
    public $tipo_espacio = 'todos';
    public $tipo_uso = 'alquiler_regular';
    public $horario_turno = 'todos';
    public $tipo_cliente = 'general';

    // Banderas
    public $tiene_taquilla = false;
    public $incluye_camerinos = false;
    public $incluye_tribunas = false;
    public $incluye_arcos_f11 = false;
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
                $description = $match->condescrip ?? $match->CONDESCRIP ?? $match->concdndesc ?? null;

                if ($description) {
                    $this->descripcion_local = trim($description);
                    
                    // Sugerir clasificaciones automáticas según el texto de Oracle
                    $textoUpper = mb_strtoupper($this->descripcion_local);
                    
                    // Detectar horario
                    if (str_contains($textoUpper, 'NOCHE')) {
                        $this->horario_turno = 'noche';
                    } elseif (str_contains($textoUpper, 'DIA') || str_contains($textoUpper, 'DÍA')) {
                        $this->horario_turno = 'dia';
                    } elseif (str_contains($textoUpper, '06:00') || str_contains($textoUpper, 'MAÑANA')) {
                        $this->horario_turno = 'madrugada_especial';
                    }

                    // Detectar tipo de espacio
                    if (str_contains($textoUpper, 'SINTETICO') || str_contains($textoUpper, 'GRASS') || str_contains($textoUpper, 'SINTETICA')) {
                        $this->tipo_espacio = 'grass_sintetico';
                    } elseif (str_contains($textoUpper, 'VOLEYBALL') || str_contains($textoUpper, 'BALONCESTO')) {
                        $this->tipo_espacio = 'losa_voley_basquet';
                    } elseif (str_contains($textoUpper, 'FUTSAL')) {
                        $this->tipo_espacio = 'losa_futsal';
                    } elseif (str_contains($textoUpper, 'FRONTÓN') || str_contains($textoUpper, 'FRONTON')) {
                        $this->tipo_espacio = 'fronton';
                    } elseif (str_contains($textoUpper, 'TENIS')) {
                        $this->tipo_espacio = 'tenis';
                    }

                    // Detectar tipo de uso
                    if (str_contains($textoUpper, 'CAMPEONATO') || str_contains($textoUpper, 'CORPORATIVO')) {
                        $this->tipo_uso = 'campeonato_corporativo';
                    } elseif (str_contains($textoUpper, 'LIGA DISTRITAL') && str_contains($textoUpper, 'ENTRENAMIENTO')) {
                        $this->tipo_uso = 'liga_entrenamiento';
                    } elseif (str_contains($textoUpper, 'LIGA DISTRITAL')) {
                        $this->tipo_uso = 'liga_oficial';
                    }

                    $this->dispatch('swal', [
                        'icon' => 'success',
                        'title' => 'Concepto Encontrado',
                        'text' => 'Descripción autocompletada y categorizada desde Oracle.',
                    ]);
                } else {
                    $this->dispatch('swal', [
                        'icon' => 'info',
                        'title' => 'Registro Encontrado',
                        'text' => 'Se encontró el registro pero sin descripción.',
                    ]);
                }
            } else {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'No Encontrado',
                    'text' => 'No se encontró ningún concepto en Oracle para el Grupo ' . $this->grupo_tusne . ' y Código ' . $this->codigo_tusne,
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Conexión',
                'text' => 'No se pudo conectar a Oracle: ' . $e->getMessage(),
            ]);
        }
    }

    public function updatingSearch() { $this->resetPage(); }

    protected function rules()
    {
        return [
            'grupo_tusne' => 'required|string|max:10',
            'codigo_tusne' => [
                'required',
                'string',
                'max:20',
                Rule::unique('catalogos_tusne')
                    ->where('grupo_tusne', $this->grupo_tusne)
                    ->ignore($this->tusneId),
            ],
            'descripcion_local' => 'required|string|max:500',
            'tipo_espacio' => 'required|in:grass_sintetico,losa_voley_basquet,losa_futsal,fronton,tenis,losa_general,todos',
            'tipo_uso' => 'required|in:alquiler_regular,campeonato_corporativo,liga_oficial,liga_entrenamiento,clase_particular,todos',
            'horario_turno' => 'required|in:dia,noche,madrugada_especial,todos',
            'tipo_cliente' => 'required|in:general,vecino,no_vecino,club_liga',
            'tiene_taquilla' => 'boolean',
            'incluye_camerinos' => 'boolean',
            'incluye_tribunas' => 'boolean',
            'incluye_arcos_f11' => 'boolean',
            'esta_activo' => 'required|boolean',
        ];
    }

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
            ->paginate(10);

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
        $this->grupo_tusne = '23';
        $this->codigo_tusne = '';
        $this->descripcion_local = '';
        $this->tipo_espacio = 'todos';
        $this->tipo_uso = 'alquiler_regular';
        $this->horario_turno = 'todos';
        $this->tipo_cliente = 'general';
        $this->tiene_taquilla = false;
        $this->incluye_camerinos = false;
        $this->incluye_tribunas = false;
        $this->incluye_arcos_f11 = false;
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
            'text' => 'El código TUSNE ha sido catalogado con éxito.',
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
        $this->tipo_espacio = $catalog->tipo_espacio;
        $this->tipo_uso = $catalog->tipo_uso;
        $this->horario_turno = $catalog->horario_turno;
        $this->tipo_cliente = $catalog->tipo_cliente;
        $this->tiene_taquilla = (bool) $catalog->tiene_taquilla;
        $this->incluye_camerinos = (bool) $catalog->incluye_camerinos;
        $this->incluye_tribunas = (bool) $catalog->incluye_tribunas;
        $this->incluye_arcos_f11 = (bool) $catalog->incluye_arcos_f11;
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
            'text' => 'El estado del concepto TUSNE ha cambiado.',
        ]);
    }
}