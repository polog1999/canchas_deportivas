<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Gestión de Canchas Deportivas</x-slot>
    <div class="max-w-7xl mx-auto">

        <!-- Encabezado de la Sección -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Canchas y Espacios Deportivos</h2>
                <p class="text-sm text-gray-600">Administra los campos de juego, losas y los conceptos TUSNE que aplican a cada cancha.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nueva Cancha
            </button>
        </div>

        <!-- Barra de Búsqueda y Filtros Combinados -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            
            <!-- Input Búsqueda por Texto -->
            <div class="relative w-full md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre de cancha o sede..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>

            <!-- Filtros desplegables -->
            <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3">
                <!-- Filtro por Sede -->
                <select wire:model.live="selectedLocationFilter"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas las Sedes</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->nombre }}</option>
                    @endforeach
                </select>

                <!-- Filtro por Deporte -->
                <select wire:model.live="selectedDeporteFilter"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos los Deportes</option>
                    @foreach($deportes as $deporte)
                        <option value="{{ $deporte->id }}">{{ $deporte->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Cargando...
            </div>
        </div>

        <!-- Tabla de Canchas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Cancha / Campo</th>
                            <th class="py-4 px-6">Sede Asignada</th>
                            <th class="py-4 px-6">Deportes</th>
                            <th class="py-4 px-6">Conceptos TUSNE Asociados</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($courts as $court)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-base">
                                            <i class="fa-solid fa-futbol"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $court->nombre }}</div>
                                            <span class="text-[11px] text-gray-400">ID: #{{ $court->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1.5 font-medium text-gray-800">
                                        <i class="fa-solid fa-location-dot text-emerald-600 text-xs"></i>
                                        <span>{{ $court->sede->nombre ?? 'Sin Sede' }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400 block mt-0.5 truncate max-w-xs">
                                        {{ $court->sede->direccion ?? '' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($court->deportes->isEmpty())
                                        <span class="text-xs text-gray-400 italic">Sin deportes</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($court->deportes as $deporte)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                                    {{ $deporte->nombre }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($court->catalogosTusne->isEmpty())
                                        <span class="text-xs text-red-500 italic font-medium">Sin TUSNE vinculado</span>
                                    @else
                                        <div class="flex flex-wrap gap-1.5 max-w-md">
                                            @foreach ($court->catalogosTusne as $tusne)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200"
                                                    title="{{ $tusne->descripcion_local }}">
                                                    <span class="font-bold text-emerald-700">Cód. {{ $tusne->codigo_tusne }}</span>
                                                    <span class="text-slate-400">|</span>
                                                    <span class="truncate max-w-[150px]">{{ $tusne->descripcion_local }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleStatus({{ $court->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $court->esta_activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-1.5 {{ $court->esta_activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                        {{ $court->esta_activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editCourt({{ $court->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar Cancha">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="toggleStatus({{ $court->id }})"
                                            class="w-8 h-8 rounded-lg {{ $court->esta_activo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} flex items-center justify-center transition-colors"
                                            title="{{ $court->esta_activo ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $court->esta_activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-regular fa-folder-open text-3xl text-gray-300"></i>
                                        <span>No se encontraron canchas registradas con los filtros seleccionados.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($courts->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $courts->links() }}
                </div>
            @endif
        </div>

        <!-- Modal de Creación / Edición -->
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
                    class="relative z-10 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">

                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $isEditMode ? 'Editar Cancha Deportiva' : 'Registrar Nueva Cancha' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveCourt">
                        <div class="bg-white px-6 py-6 space-y-5 max-h-[75vh] overflow-y-auto">

                            <!-- Sede a la que pertenece -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Sede / Complejo Deportivo <span class="text-red-500">*</span></label>
                                <select wire:model="sede_id"
                                    class="w-full px-3 py-2 border @error('sede_id') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <option value="">-- Seleccione la Sede --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->nombre }} - {{ $loc->direccion }}</option>
                                    @endforeach
                                </select>
                                @error('sede_id')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Nombre de la Cancha -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre del Espacio / Campo <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nombre"
                                    class="w-full px-3 py-2 border @error('nombre') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Ej: Cancha Principal de Gras Sintético N° 1" />
                                @error('nombre')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Deportes (canchas_deportes) -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Deportes / Disciplinas Permitidas <span class="text-red-500">*</span></label>
                                <div class="max-h-36 overflow-y-auto rounded-lg border @error('deporte_ids') border-red-500 @else border-gray-300 @enderror p-3 space-y-1.5 bg-white">
                                    @forelse ($deportes as $deporte)
                                        <label class="flex items-center gap-2.5 text-sm text-gray-700 cursor-pointer hover:bg-slate-50 p-1 rounded">
                                            <input type="checkbox" wire:model="deporte_ids" value="{{ $deporte->id }}"
                                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span>{{ $deporte->nombre }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-400">No hay deportes registrados.</p>
                                    @endforelse
                                </div>
                                @error('deporte_ids')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Conceptos TUSNE Múltiples (canchas_tusne) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-gray-700">Conceptos TUSNE Aplicables <span class="text-red-500">*</span></label>
                                    <span class="text-[11px] text-emerald-700 font-medium">Seleccione todos los TUSNEs con los que se puede pagar esta cancha</span>
                                </div>

                                <div class="max-h-52 overflow-y-auto rounded-lg border @error('catalogo_tusne_ids') border-red-500 @else border-gray-300 @enderror p-2.5 space-y-2 bg-white">
                                    @forelse ($tusnes as $t)
                                        <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 border border-slate-100 hover:border-slate-200 cursor-pointer transition">
                                            <input type="checkbox" wire:model="catalogo_tusne_ids" value="{{ $t->id }}"
                                                class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <div class="text-xs flex-grow">
                                                <div class="flex items-center gap-2 flex-wrap justify-between">
                                                    <span class="font-semibold text-gray-900">{{ $t->descripcion_local }}</span>
                                                    <span class="bg-emerald-50 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                                                        Cód: {{ $t->codigo_tusne }} (G: {{ $t->grupo_tusne }})
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap gap-2">
                                                    <span class="capitalize">Horario: <strong>{{ $t->modificador_tiempo }}</strong></span>
                                                    <span>·</span>
                                                    <span class="capitalize">Tipo: <strong>{{ $t->tipo_cliente }}</strong></span>
                                                    @if($t->tiene_recaudacion_taquilla)
                                                        <span>·</span>
                                                        <span class="text-amber-600 font-medium">Con Taquilla</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-400 p-2">No hay conceptos TUSNE registrados en el catálogo.</p>
                                    @endforelse
                                </div>
                                @error('catalogo_tusne_ids')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Estado -->
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
                                <span wire:loading wire:target="saveCourt">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                                <span>{{ $isEditMode ? 'Guardar Cambios' : 'Registrar Cancha' }}</span>
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