<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Slider Home</x-slot>
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Slider de inicio</h2>
                <p class="text-sm text-gray-600">Administra las imágenes y textos del carrusel de la página pública.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nuevo slide
            </button>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por título..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Slide</th>
                            <th class="py-4 px-6">Botón</th>
                            <th class="py-4 px-6">Orden</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($sliders as $slider)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $slider->urlImagen() }}" alt="{{ $slider->titulo }}"
                                            class="w-20 h-12 rounded-lg object-cover border border-gray-100 bg-gray-50">
                                        <div>
                                            <div class="font-bold text-gray-900 line-clamp-2">{{ $slider->titulo }}</div>
                                            <span class="text-[11px] text-gray-400">ID: #{{ $slider->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-gray-800">{{ $slider->texto_boton }}</div>
                                    <div class="text-[11px] text-gray-400 truncate max-w-[12rem]">{{ $slider->enlace_boton }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $slider->orden }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleActivo({{ $slider->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $slider->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $slider->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editSlider({{ $slider->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="deleteSlider({{ $slider->id }})"
                                            wire:confirm="¿Eliminar este slide?"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors"
                                            title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                    No hay slides registrados. Crea el primero para el home.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sliders->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $sliders->links() }}
                </div>
            @endif
        </div>

        <div x-data="{ show: @entangle('isOpen') }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false; $wire.closeModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-10 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $isEditMode ? 'Editar slide' : 'Nuevo slide' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveSlider">
                        <div class="bg-white px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                                <textarea wire:model.live="titulo" rows="3" maxlength="120"
                                    class="w-full px-3 py-2 border @error('titulo') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Texto principal del slide"></textarea>
                                <div class="flex items-start justify-between gap-2 mt-1">
                                    @error('titulo')
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">Máximo 120 caracteres para que no se desborde el slider.</span>
                                    @enderror
                                    <span class="text-xs shrink-0 {{ mb_strlen($titulo) > 120 ? 'text-red-500' : 'text-gray-400' }}">
                                        {{ mb_strlen($titulo) }}/120
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Texto botón <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="texto_boton" maxlength="40"
                                        class="w-full px-3 py-2 border @error('texto_boton') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                        placeholder="Reservar cancha" />
                                    @error('texto_boton')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Orden <span class="text-red-500">*</span></label>
                                    <input type="number" min="0" wire:model="orden"
                                        class="w-full px-3 py-2 border @error('orden') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500" />
                                    @error('orden')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Enlace botón <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="enlace_boton"
                                    class="w-full px-3 py-2 border @error('enlace_boton') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                    placeholder="#gridSedes o /ruta" />
                                @error('enlace_boton')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Estado</label>
                                <select wire:model="activo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-1 focus:ring-emerald-500">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Imagen
                                    @unless ($isEditMode)
                                        <span class="text-red-500">*</span>
                                    @endunless
                                </label>
                                @if ($imagenActual && ! $imagenNueva)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/imagenes/slider/' . $imagenActual) }}" alt="Imagen actual"
                                            class="w-full h-36 object-cover rounded-lg border border-gray-200">
                                    </div>
                                @endif
                                <input type="file" wire:model="imagenNueva" accept="image/*"
                                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-semibold hover:file:bg-emerald-100" />
                                <p class="text-[11px] text-gray-400 mt-1">JPG, PNG o WEBP · máx. 5 MB · <code>public/imagenes/slider/</code></p>
                                @error('imagenNueva')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                                @if ($imagenNueva)
                                    <button type="button" wire:click="removeImagenNueva" class="mt-2 text-xs text-red-600 hover:underline">Quitar imagen nueva</button>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                            <button type="button" @click="show = false; $wire.closeModal()"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-white">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
