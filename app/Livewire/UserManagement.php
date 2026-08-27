<?php

namespace App\Livewire;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $isOpen = false;
    public $isEditMode = false;

    public $userId;
    public $usuario;
    public $correo_electronico;
    public $clave;
    public $activo = true;
    public $rol_id;

    public $profileId;
    public $tipo_documento;
    public $numero_documento;
    public $nombres;
    public $apellido_paterno;
    public $apellido_materno;
    public $direccion;
    public $ubigeo_departamento;
    public $ubigeo_provincia;
    public $ubigeo_distrito;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'usuario' => [
                'required',
                'string',
                'max:50',
                Rule::unique('usuarios', 'usuario')->ignore($this->userId),
            ],
            'correo_electronico' => [
                'nullable',
                'email',
                'max:150',
            ],
            'clave' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'activo' => 'required|boolean',
            'rol_id' => ['required', 'exists:roles,id'],
            'tipo_documento' => 'nullable|string|max:3',
            'numero_documento' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('perfiles', 'numero_documento')->ignore($this->profileId),
            ],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'ubigeo_departamento' => 'nullable|string|max:255',
            'ubigeo_provincia' => 'nullable|string|max:255',
            'ubigeo_distrito' => 'nullable|string|max:255',
        ];
    }

    protected $validationAttributes = [
        'usuario' => 'usuario',
        'correo_electronico' => 'correo electrónico',
        'clave' => 'contraseña',
        'rol_id' => 'rol',
        'numero_documento' => 'número de documento',
        'nombres' => 'nombres',
        'apellido_paterno' => 'apellido paterno',
        'apellido_materno' => 'apellido materno',
    ];

    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->profileId = null;
        $this->usuario = '';
        $this->correo_electronico = '';
        $this->clave = '';
        $this->activo = true;
        $this->rol_id = '';

        $this->tipo_documento = '';
        $this->numero_documento = '';
        $this->nombres = '';
        $this->apellido_paterno = '';
        $this->apellido_materno = '';
        $this->direccion = '';
        $this->ubigeo_departamento = '';
        $this->ubigeo_provincia = '';
        $this->ubigeo_distrito = '';

        $this->isEditMode = false;
    }

    public function saveUser()
    {
        $this->validate();

        $profileData = [
            'tipo_documento' => $this->tipo_documento ?: null,
            'numero_documento' => $this->numero_documento ?: null,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'direccion' => $this->direccion,
            'ubigeo_departamento' => $this->ubigeo_departamento,
            'ubigeo_provincia' => $this->ubigeo_provincia,
            'ubigeo_distrito' => $this->ubigeo_distrito,
        ];

        if ($this->isEditMode) {
            $user = Usuario::findOrFail($this->userId);
            $user->update([
                'usuario' => $this->usuario,
                'correo_electronico' => $this->correo_electronico,
                'activo' => $this->activo,
                'rol_id' => $this->rol_id,
            ]);

            if ($this->clave) {
                $user->update(['clave' => $this->clave]);
            }

            $user->perfil()->updateOrCreate(
                ['id' => $this->profileId],
                $profileData
            );

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Actualizado!',
                'text' => 'El usuario y su perfil se han actualizado con éxito.',
            ]);
        } else {
            $user = Usuario::create([
                'usuario' => $this->usuario,
                'correo_electronico' => $this->correo_electronico,
                'clave' => $this->clave,
                'activo' => $this->activo,
                'rol_id' => $this->rol_id,
            ]);

            $user->perfil()->create($profileData);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Creado!',
                'text' => 'El usuario ha sido registrado correctamente.',
            ]);
        }

        $this->closeModal();
    }

    public function editUser($id)
    {
        $this->resetErrorBag();
        $this->isEditMode = true;

        $user = Usuario::with(['perfil', 'rol'])->findOrFail($id);
        $this->userId = $user->id;
        $this->usuario = $user->usuario;
        $this->correo_electronico = $user->correo_electronico;
        $this->activo = $user->activo;
        $this->clave = '';
        $this->rol_id = $user->rol_id;

        if ($user->perfil) {
            $this->profileId = $user->perfil->id;
            $this->tipo_documento = $user->perfil->tipo_documento?->value;
            $this->numero_documento = $user->perfil->numero_documento;
            $this->nombres = $user->perfil->nombres;
            $this->apellido_paterno = $user->perfil->apellido_paterno;
            $this->apellido_materno = $user->perfil->apellido_materno;
            $this->direccion = $user->perfil->direccion;
            $this->ubigeo_departamento = $user->perfil->ubigeo_departamento;
            $this->ubigeo_provincia = $user->perfil->ubigeo_provincia;
            $this->ubigeo_distrito = $user->perfil->ubigeo_distrito;
        }

        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $user = Usuario::findOrFail($id);
        $user->activo = ! $user->activo;
        $user->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Cambiado',
            'text' => 'El estado del usuario se ha actualizado de forma exitosa.',
        ]);
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        $users = Usuario::with(['perfil', 'rol'])
            ->where(function ($query) {
                $query->where('usuario', 'ilike', '%' . $this->search . '%')
                    ->orWhere('correo_electronico', 'ilike', '%' . $this->search . '%')
                    ->orWhereHas('perfil', function ($q) {
                        $q->where('numero_documento', 'ilike', '%' . $this->search . '%')
                            ->orWhere('nombres', 'ilike', '%' . $this->search . '%')
                            ->orWhere('apellido_paterno', 'ilike', '%' . $this->search . '%')
                            ->orWhere('apellido_materno', 'ilike', '%' . $this->search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $roles = Rol::where('activo', true)->orderBy('nombre')->get();

        return view('livewire.user-management', compact('users', 'roles'));
    }
}
