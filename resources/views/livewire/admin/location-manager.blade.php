<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Gestión de Sedes Deportivo</x-slot>
    <div class="max-w-7xl mx-auto">

        <!-- Encabezado de la Sección -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Sedes y Complejos Deportivos</h2>
                <p class="text-sm text-gray-600">Administra los lugares físicos y ubicaciones donde se encuentran las canchas del municipio.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nueva Sede
            </button>
        </div>

        <!-- Barra de Búsqueda -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o dirección..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Buscando sedes...
            </div>
        </div>

        <!-- Tabla de Sedes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Sede / Complejo</th>
                            <th class="py-4 px-6">Dirección Física</th>
                            <th class="py-4 px-6">Ubicación Maps</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($locations as $location)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-base">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </div>
                                        <div class="font-bold text-gray-900">
                                            {{ $location->nombre }}
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-gray-600 flex items-center gap-1.5">
                                        <i class="fa-solid fa-map-pin text-gray-400 text-xs"></i>
                                        <span>{{ $location->direccion }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    @if($location->enlace_mapas)
                                        <a href="{{ $location->enlace_mapas }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-md border border-emerald-100 transition-colors">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            Ver Mapa
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Sin enlace</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleStatus({{ $location->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $location->esta_activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-1.5 {{ $location->esta_activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                        {{ $location->esta_activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editLocation({{ $location->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar Sede">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="toggleStatus({{ $location->id }})"
                                            class="w-8 h-8 rounded-lg {{ $location->esta_activo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} flex items-center justify-center transition-colors"
                                            title="{{ $location->esta_activo ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $location->esta_activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-regular fa-folder-open text-3xl text-gray-300"></i>
                                        <span>No se encontraron sedes registradas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($locations->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $locations->links() }}
                </div>
            @endif
        </div>

        <!-- Modal de Creación / Edición -->
        <div x-data="{ show: @entangle('isOpen') }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                <!-- Fondo traslúcido -->
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false; $wire.closeModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Contenido del Modal -->
                <div x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-10 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">

                    <!-- Cabecera del Modal -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $isEditMode ? 'Editar Datos de la Sede' : 'Registrar Nueva Sede' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Formulario -->
                    <form wire:submit.prevent="saveLocation">
                        <div class="bg-white px-6 py-6 space-y-4">

                            <!-- Nombre de la Sede -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre de la Sede / Complejo <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nombre"
                                    class="w-full px-3 py-2 border @error('nombre') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ej: Sede Norte - Los Jalapeños" />
                                @error('nombre')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Dirección Física -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Dirección Exacta <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="direccion"
                                    class="w-full px-3 py-2 border @error('direccion') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ej: Av. La Molina 1230, La Molina" />
                                @error('direccion')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Enlace Google Maps -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Enlace de Ubicación (Google Maps) <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="enlace_mapas"
                                    class="w-full px-3 py-2 border @error('enlace_mapas') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ej: https://maps.google.com/..." />
                                <span class="text-[11px] text-gray-400 mt-0.5 block">Pegue el enlace compartido directamente desde Google Maps.</span>
                                @error('enlace_mapas')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Estado Activo -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Estado de Disponibilidad</label>
                                <select wire:model="esta_activo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <option value="1">Habilitado (Disponible para reservas)</option>
                                    <option value="0">Inhabilitado (Ocultar)</option>
                                </select>
                            </div>

                        </div>

                        <!-- Botones de Acción -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-2">
                            <button type="button" @click="show = false; $wire.closeModal()"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow focus:outline-none transition-colors flex items-center justify-center gap-1.5">
                                <span wire:loading wire:target="saveLocation">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                                <span>{{ $isEditMode ? 'Guardar Cambios' : 'Registrar Sede' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de SweetAlert2 -->
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