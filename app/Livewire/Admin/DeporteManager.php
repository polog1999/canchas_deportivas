<?php

namespace App\Livewire\Admin;

use App\Models\Deporte;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DeporteManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $deporteId;

    public $nombre = '';
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
            'imagenNueva' => 'nullable|image|max:4096',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre del deporte es obligatorio.',
        'imagenNueva.image' => 'El archivo debe ser una imagen (jpg, png, webp, etc.).',
        'imagenNueva.max' => 'La imagen no debe superar los 4 MB.',
    ];

    #[Layout('components.app-layout')]
    public function render()
    {
        $deportes = Deporte::query()
            ->withCount('canchas')
            ->when($this->search, function ($query) {
                $query->where('nombre', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.deporte-manager', [
            'deportes' => $deportes,
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
        $this->deporteId = null;
        $this->isEditMode = false;
        $this->nombre = '';
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

    public function saveDeporte()
    {
        $this->validate();

        $deporte = Deporte::updateOrCreate(
            ['id' => $this->deporteId],
            ['nombre' => $this->nombre]
        );

        if ($this->imagenNueva) {
            File::ensureDirectoryExists(public_path('imagenes/deportes'));

            $extension = strtolower($this->imagenNueva->getClientOriginalExtension() ?: 'jpg');
            $filename = 'deporte_' . $deporte->id . '_' . Str::lower(Str::random(8)) . '.' . $extension;

            $this->eliminarArchivoLocal($deporte->imagen);
            $this->imagenNueva->storeAs('', $filename, 'deportes');

            $deporte->imagen = $filename;
            $deporte->save();
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Deporte Actualizado' : 'Deporte Registrado',
            'text' => 'La información del deporte se ha guardado correctamente.',
        ]);

        $this->closeModal();
    }

    public function editDeporte($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->deporteId = $id;

        $deporte = Deporte::findOrFail($id);
        $this->nombre = $deporte->nombre;
        $this->imagenActual = $deporte->imagen;
        $this->imagenNueva = null;
        $this->isOpen = true;
    }

    public function deleteDeporte($id)
    {
        $deporte = Deporte::withCount('canchas')->findOrFail($id);

        if ($deporte->canchas_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => 'Este deporte está asociado a ' . $deporte->canchas_count . ' cancha(s). Desvincúlalo primero.',
            ]);

            return;
        }

        $this->eliminarArchivoLocal($deporte->imagen);
        $deporte->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Deporte eliminado',
            'text' => 'El registro se eliminó correctamente.',
        ]);
    }

    private function eliminarArchivoLocal(?string $imagen): void
    {
        $imagen = trim((string) $imagen);
        if ($imagen === '' || preg_match('#^(https?:)?//#i', $imagen)) {
            return;
        }

        $path = public_path('imagenes/deportes/' . basename($imagen));
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
