<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Gestión de Deportes</x-slot>
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Deportes / Disciplinas</h2>
                <p class="text-sm text-gray-600">Administra los deportes que luego se vinculan a las canchas.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nuevo Deporte
            </button>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Buscando...
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Deporte</th>
                            <th class="py-4 px-6">Canchas vinculadas</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($deportes as $deporte)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $deporte->urlImagen() }}" alt="{{ $deporte->nombre }}"
                                            class="w-12 h-12 rounded-lg object-cover border border-gray-100 bg-gray-50">
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $deporte->nombre }}</div>
                                            <span class="text-[11px] text-gray-400">ID: #{{ $deporte->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $deporte->canchas_count }}
                                        {{ $deporte->canchas_count === 1 ? 'cancha' : 'canchas' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editDeporte({{ $deporte->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="deleteDeporte({{ $deporte->id }})"
                                            wire:confirm="¿Eliminar este deporte?"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors"
                                            title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-6 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-regular fa-folder-open text-3xl text-gray-300"></i>
                                        <span>No se encontraron deportes registrados.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($deportes->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $deportes->links() }}
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
                            {{ $isEditMode ? 'Editar Deporte' : 'Registrar Nuevo Deporte' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveDeporte">
                        <div class="bg-white px-6 py-6 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nombre"
                                    class="w-full px-3 py-2 border @error('nombre') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ej: Fútbol 11" />
                                @error('nombre')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Imagen</label>
                                <div class="flex flex-col sm:flex-row gap-4 items-start">
                                    <div class="w-28 h-20 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 shrink-0 flex items-center justify-center">
                                        @if ($imagenNueva)
                                            <img src="{{ $imagenNueva->temporaryUrl() }}" alt="Vista previa"
                                                class="w-full h-full object-cover">
                                        @elseif ($imagenActual)
                                            <img src="{{ asset('storage/imagenes/deportes/' . $imagenActual) }}" alt="Imagen actual"
                                                class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-regular fa-image text-gray-300 text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 w-full space-y-2">
                                        <input type="file" wire:model="imagenNueva" accept="image/*"
                                            class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                                        <p class="text-[11px] text-gray-400">JPG, PNG o WEBP · máx. 4 MB · <code>public/imagenes/deportes/</code></p>
                                        <div wire:loading wire:target="imagenNueva" class="text-xs text-emerald-600">
                                            <i class="fa-solid fa-spinner animate-spin mr-1"></i> Subiendo vista previa...
                                        </div>
                                        @if ($imagenNueva)
                                            <button type="button" wire:click="removeImagenNueva"
                                                class="text-xs font-semibold text-red-600 hover:underline">
                                                Quitar imagen seleccionada
                                            </button>
                                        @endif
                                        @error('imagenNueva')
                                            <span class="text-xs text-red-500 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-2">
                            <button type="button" @click="show = false; $wire.closeModal()"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow focus:outline-none transition-colors flex items-center justify-center gap-1.5">
                                <span wire:loading wire:target="saveDeporte">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                                <span>{{ $isEditMode ? 'Guardar Cambios' : 'Registrar Deporte' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('swal', event => {
                const payload = event.detail[0] || event.detail;
                Swal.fire({
                    icon: payload.icon || 'success',
                    title: payload.title || 'Operación Exitosa',
                    text: payload.text || '',
                    confirmButtonColor: '#059669',
                });
            });
        });
    </script>
</div>
