<?php

namespace App\Livewire\Admin;

use App\Models\Sede;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $locationId;

    public $nombre = '';
    public $direccion = '';
    public $enlace_mapas = '';
    public $hora_inicio = '08:00';
    public $hora_fin = '22:00';
    public $esta_activo = true;
    public $imagenActual = null;
    public $imagenNueva = null;

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
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'esta_activo' => 'required|boolean',
            'imagenNueva' => 'nullable|image|max:4096',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la sede es obligatorio.',
        'direccion.required' => 'La dirección física es obligatoria.',
        'enlace_mapas.required' => 'El enlace de Google Maps es obligatorio.',
        'hora_inicio.required' => 'La hora de inicio es obligatoria.',
        'hora_inicio.date_format' => 'Usa el formato HH:MM para la hora de inicio.',
        'hora_fin.required' => 'La hora de fin es obligatoria.',
        'hora_fin.date_format' => 'Usa el formato HH:MM para la hora de fin.',
        'hora_fin.after' => 'La hora de fin debe ser posterior a la de inicio.',
        'imagenNueva.image' => 'El archivo debe ser una imagen (jpg, png, webp, etc.).',
        'imagenNueva.max' => 'La imagen no debe superar los 4 MB.',
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
        $this->hora_inicio = '08:00';
        $this->hora_fin = '22:00';
        $this->esta_activo = true;
        $this->imagenActual = null;
        $this->imagenNueva = null;
        $this->resetValidation();
    }

    public function updatedImagenNueva()
    {
        $this->validateOnly('imagenNueva');
    }

    public function removeImagenNueva()
    {
        $this->imagenNueva = null;
    }

    public function saveLocation()
    {
        $validatedData = $this->validate();
        unset($validatedData['imagenNueva']);

        $sede = Sede::updateOrCreate(
            ['id' => $this->locationId],
            $validatedData
        );

        if ($this->imagenNueva) {

            $extension = strtolower(
                $this->imagenNueva->getClientOriginalExtension() ?: 'jpg'
            );

            $filename = 'sede_' . $sede->id . '_' . Str::lower(Str::random(8)) . '.' . $extension;

            // Eliminar imagen anterior de Storage
            $this->eliminarArchivoLocal($sede->imagen);

            // Guardar en:
            // storage/app/public/imagenes/sedes/
            $this->imagenNueva->storeAs(
                'imagenes/sedes',
                $filename,
                'public'
            );

            // Guardamos solamente el nombre en la BD
            $sede->imagen = $filename;
            $sede->save();
        }

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
        $this->hora_inicio = $location->hora_inicio
            ? substr((string) $location->hora_inicio, 0, 5)
            : '08:00';
        $this->hora_fin = $location->hora_fin
            ? substr((string) $location->hora_fin, 0, 5)
            : '22:00';
        $this->esta_activo = (bool) $location->esta_activo;
        $this->imagenActual = $location->imagen;
        $this->imagenNueva = null;

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

    private function eliminarArchivoLocal(?string $imagen): void
    {
        $imagen = trim((string) $imagen);

        if ($imagen === '' || preg_match('#^(https?:)?//#i', $imagen)) {
            return;
        }

        $name = basename($imagen);

        Storage::disk('public')->delete('imagenes/sedes/' . $name);
    }
}
