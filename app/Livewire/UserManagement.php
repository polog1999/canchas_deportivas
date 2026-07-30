<?php

namespace App\Livewire;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Profile;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

class UserManagement extends Component
{
    use WithPagination;

    // Filtros de búsqueda
    public $search = '';

    // Estado del Modal
    public $isOpen = false;
    public $isEditMode = false;

    // Propiedades del Modelo User
    public $userId;
    public $name;
    public $email;
    public $password;
    public $activo = true;

    // Propiedades del Modelo Profile
    public $profileId;
    public $document_type;
    public $document_number;
    public $names;
    public $last_name_paternal;
    public $last_name_maternal;
    public $address;
    public $ubigeo_department;
    public $ubigeo_province;
    public $ubigeo_district;
    public $role;

    // Resetear paginación al realizar búsquedas
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Reglas de validación dinámicas
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'activo' => 'required|boolean',
            'document_type' => 'nullable|string|max:3',
            'document_number' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('profiles', 'document_number')->ignore($this->profileId),
            ],
            'names' => 'nullable|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'last_name_maternal' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'ubigeo_department' => 'nullable|string|max:255',
            'ubigeo_province' => 'nullable|string|max:255',
            'ubigeo_district' => 'nullable|string|max:255',
            'role' => ['nullable',Rule::enum(UserRole::class)],
        ];
    }

    protected $validationAttributes = [
        'name' => 'usuario',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'document_number' => 'número de documento',
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
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->activo = true;
        
        $this->document_type = '';
        $this->document_number = '';
        $this->names = '';
        $this->last_name_paternal = '';
        $this->last_name_maternal = '';
        $this->address = '';
        $this->ubigeo_department = '';
        $this->ubigeo_province = '';
        $this->ubigeo_district = '';
        $this->role = '';
        
        $this->isEditMode = false;
    }

    public function saveUser()
    {
        $this->validate();

        if ($this->isEditMode) {
            // Actualizar Usuario existente
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'activo' => $this->activo,
            ]);

            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            // Actualizar o crear Perfil relacionado
            $user->profile()->updateOrCreate(
                ['id' => $this->profileId],
                [
                    'document_type' => $this->document_type,
                    'document_number' => $this->document_number,
                    'names' => $this->names,
                    'last_name_paternal' => $this->last_name_paternal,
                    'last_name_maternal' => $this->last_name_maternal,
                    'address' => $this->address,
                    'ubigeo_department' => $this->ubigeo_department,
                    'ubigeo_province' => $this->ubigeo_province,
                    'ubigeo_district' => $this->ubigeo_district,
                ]
            );
            $user->syncRoles($this->role); // El usuario ahora es administrador

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Actualizado!',
                'text' => 'El usuario y su perfil se han actualizado con éxito.',
            ]);

        } else {
            // Crear nuevo Usuario
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'activo' => $this->activo,
            ]);

            // Crear Perfil asociado
            $user->profile()->create([
                'document_type' => $this->document_type,
                'document_number' => $this->document_number,
                'names' => $this->names,
                'last_name_paternal' => $this->last_name_paternal,
                'last_name_maternal' => $this->last_name_maternal,
                'address' => $this->address,
                'ubigeo_department' => $this->ubigeo_department,
                'ubigeo_province' => $this->ubigeo_province,
                'ubigeo_district' => $this->ubigeo_district,
            ]);
            $user-syncRoles($this->role); // El usuario ahora es administrador

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
        
        $user = User::with('profile')->findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->activo = $user->activo;
        $this->password = ''; // Vacío por seguridad
        $this->role = $user->getRoleNames()->first() ?? 'Sin Rol';
        if ($user->profile) {
            $this->profileId = $user->profile->id;
            $this->document_type = $user->profile->document_type?->value;
            $this->document_number = $user->profile->document_number;
            $this->names = $user->profile->names;
            $this->last_name_paternal = $user->profile->last_name_paternal;
            $this->last_name_maternal = $user->profile->last_name_maternal;
            $this->address = $user->profile->address;
            $this->ubigeo_department = $user->profile->ubigeo_department;
            $this->ubigeo_province = $user->profile->ubigeo_province;
            $this->ubigeo_district = $user->profile->ubigeo_district;
        }

        $this->isOpen = true;
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->activo = !$user->activo;
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
        $users = User::with('profile')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('profile', function ($q) {
                        $q->where('document_number', 'like', '%' . $this->search . '%')
                          ->orWhere('names', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name_paternal', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.user-management', compact('users'));
    }
}