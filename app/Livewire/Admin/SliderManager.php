<?php

namespace App\Livewire\Admin;

use App\Models\Slider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SliderManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $isOpen = false;
    public $isEditMode = false;
    public $sliderId;

    public $titulo = '';
    public $texto_boton = 'Reservar cancha';
    public $enlace_boton = '#gridSedes';
    public $orden = 1;
    public $activo = true;
    public $imagenActual = null;
    public $imagenNueva = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'texto_boton' => 'required|string|max:100',
            'enlace_boton' => 'required|string|max:255',
            'orden' => 'required|integer|min:0',
            'activo' => 'required|boolean',
            'imagenNueva' => ($this->isEditMode ? 'nullable' : 'required') . '|image|max:5120',
        ];
    }

    protected $messages = [
        'titulo.required' => 'El título del slide es obligatorio.',
        'texto_boton.required' => 'El texto del botón es obligatorio.',
        'enlace_boton.required' => 'El enlace del botón es obligatorio.',
        'imagenNueva.required' => 'La imagen del slide es obligatoria.',
        'imagenNueva.image' => 'El archivo debe ser una imagen (jpg, png, webp, etc.).',
        'imagenNueva.max' => 'La imagen no debe superar los 5 MB.',
    ];

    #[Layout('components.app-layout')]
    public function render()
    {
        $sliders = Slider::query()
            ->when($this->search, function ($query) {
                $query->where('titulo', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('orden')
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.admin.slider-manager', [
            'sliders' => $sliders,
        ]);
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->orden = (int) Slider::max('orden') + 1;
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->sliderId = null;
        $this->isEditMode = false;
        $this->titulo = '';
        $this->texto_boton = 'Reservar cancha';
        $this->enlace_boton = '#gridSedes';
        $this->orden = 1;
        $this->activo = true;
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

    public function saveSlider()
    {
        $this->validate();

        $slider = Slider::updateOrCreate(
            ['id' => $this->sliderId],
            [
                'titulo' => $this->titulo,
                'texto_boton' => $this->texto_boton,
                'enlace_boton' => $this->enlace_boton,
                'orden' => $this->orden,
                'activo' => $this->activo,
            ]
        );

        if ($this->imagenNueva) {
            // 1. Generar nombre único para la imagen
            $extension = strtolower($this->imagenNueva->getClientOriginalExtension() ?: 'jpg');
            $filename = 'slider_' . $slider->id . '_' . Str::lower(Str::random(8)) . '.' . $extension;

            // 2. Eliminar la imagen anterior si existe en el storage public
            if ($slider->imagen && Storage::disk('public')->exists('imagenes/slider/' . $slider->imagen)) {
                Storage::disk('public')->delete('imagenes/slider/' . $slider->imagen);
            }

            // 3. Guardar la nueva imagen en storage/app/public/imagenes/slider
            $this->imagenNueva->storeAs('imagenes/slider', $filename, 'public');

            // 4. Actualizar el nombre en la base de datos
            $slider->imagen = $filename;
            $slider->save();
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEditMode ? 'Slide actualizado' : 'Slide registrado',
            'text' => 'La información del slider se guardó correctamente.',
        ]);

        $this->closeModal();
    }

    public function editSlider($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        $this->sliderId = $id;

        $slider = Slider::findOrFail($id);
        $this->titulo = $slider->titulo;
        $this->texto_boton = $slider->texto_boton;
        $this->enlace_boton = $slider->enlace_boton;
        $this->orden = $slider->orden;
        $this->activo = (bool) $slider->activo;
        $this->imagenActual = $slider->imagen;
        $this->imagenNueva = null;
        $this->isOpen = true;
    }

    public function toggleActivo($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->activo = ! $slider->activo;
        $slider->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => 'El slide se ' . ($slider->activo ? 'activó' : 'desactivó') . ' correctamente.',
        ]);
    }

    public function deleteSlider($id)
    {
        $slider = Slider::findOrFail($id);
        $this->eliminarArchivoLocal($slider->imagen);
        $slider->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Slide eliminado',
            'text' => 'El registro se eliminó correctamente.',
        ]);
    }

    private function eliminarArchivoLocal(?string $imagen): void
    {
        $imagen = trim((string) $imagen);
        if ($imagen === '' || preg_match('#^(https?:)?//#i', $imagen)) {
            return;
        }

        $path = 'imagenes/slider/' . basename($imagen);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
