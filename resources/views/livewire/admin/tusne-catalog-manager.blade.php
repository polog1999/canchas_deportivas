<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Configuración de Catálogo TUSNE</x-slot>
    <div class="max-w-7xl mx-auto">

        <!-- Encabezado -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Catálogo de Conceptos TUSNE</h2>
                <p class="text-sm text-gray-600">Configura y clasifica los códigos de Oracle para que el sistema seleccione automáticamente el precio según la cancha, horario y tipo de evento.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nuevo TUSNE
            </button>
        </div>

        <!-- Buscador -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por descripción, código..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Consultando registros...
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Código Oracle</th>
                            <th class="py-4 px-6">Descripción Oficial</th>
                            <th class="py-4 px-6">Tipo de Espacio</th>
                            <th class="py-4 px-6">Modalidad y Turno</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($catalogs as $catalog)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1.5">
                                        <span class="bg-emerald-50 text-emerald-800 text-xs px-2.5 py-1 rounded-md font-bold border border-emerald-100">
                                            G: {{ $catalog->grupo_tusne }}
                                        </span>
                                        <span class="bg-slate-100 text-slate-800 text-xs px-2.5 py-1 rounded-md font-bold border border-slate-200">
                                            {{ $catalog->codigo_tusne }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-gray-900 max-w-sm md:max-w-md text-xs leading-relaxed"
                                        title="{{ $catalog->descripcion_local }}">
                                        {{ $catalog->descripcion_local }}
                                    </div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($catalog->tiene_taquilla)
                                            <span class="text-[10px] text-amber-700 font-bold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Con Taquilla</span>
                                        @endif
                                        @if($catalog->incluye_camerinos)
                                            <span class="text-[10px] text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">Camerino</span>
                                        @endif
                                        @if($catalog->incluye_tribunas)
                                            <span class="text-[10px] text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">Tribuna</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $espacios = [
                                            'grass_sintetico' => ['label' => 'Grass Sintético', 'color' => 'bg-emerald-100 text-emerald-800'],
                                            'losa_voley_basquet' => ['label' => 'Vóley / Básquet', 'color' => 'bg-sky-100 text-sky-800'],
                                            'losa_futsal' => ['label' => 'Futsal', 'color' => 'bg-indigo-100 text-indigo-800'],
                                            'fronton' => ['label' => 'Frontón', 'color' => 'bg-amber-100 text-amber-800'],
                                            'tenis' => ['label' => 'Tenis', 'color' => 'bg-purple-100 text-purple-800'],
                                            'losa_general' => ['label' => 'Losa Multiuso', 'color' => 'bg-teal-100 text-teal-800'],
                                            'todos' => ['label' => 'Cualquier Cancha', 'color' => 'bg-slate-100 text-slate-700'],
                                        ];
                                        $espacioActual = $espacios[$catalog->tipo_espacio] ?? ['label' => $catalog->tipo_espacio, 'color' => 'bg-gray-100 text-gray-700'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $espacioActual['color'] }}">
                                        {{ $espacioActual['label'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-xs space-y-1">
                                        <div>
                                            <span class="font-semibold text-gray-500">Uso:</span>
                                            @if($catalog->tipo_uso === 'campeonato_corporativo')
                                                <span class="text-purple-700 font-bold">Campeonato / Corporativo</span>
                                            @elseif($catalog->tipo_uso === 'liga_oficial')
                                                <span class="text-blue-700 font-bold">Liga Distrital (Oficial)</span>
                                            @elseif($catalog->tipo_uso === 'liga_entrenamiento')
                                                <span class="text-teal-700 font-medium">Liga (Entrenamiento)</span>
                                            @elseif($catalog->tipo_uso === 'clase_particular')
                                                <span class="text-rose-700 font-medium">Clase / Academia</span>
                                            @else
                                                <span class="text-slate-700">Alquiler Regular</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-500">Turno:</span>
                                            @if($catalog->horario_turno === 'dia')
                                                <span class="text-amber-700 font-medium"><i class="fa-solid fa-sun text-[10px]"></i> Diurno</span>
                                            @elseif($catalog->horario_turno === 'noche')
                                                <span class="text-indigo-800 font-medium"><i class="fa-solid fa-moon text-[10px]"></i> Nocturno</span>
                                            @elseif($catalog->horario_turno === 'madrugada_especial')
                                                <span class="text-sky-700 font-medium"><i class="fa-solid fa-clock text-[10px]"></i> 06-07 AM</span>
                                            @else
                                                <span class="text-slate-500">Todo el día</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleStatus({{ $catalog->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $catalog->esta_activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-1.5 {{ $catalog->esta_activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                        {{ $catalog->esta_activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="editTusne({{ $catalog->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar Concepto">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="toggleStatus({{ $catalog->id }})"
                                            class="w-8 h-8 rounded-lg {{ $catalog->esta_activo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} flex items-center justify-center transition-colors"
                                            title="{{ $catalog->esta_activo ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa-solid {{ $catalog->esta_activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-gray-500">
                                    No se encontraron registros TUSNE.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($catalogs->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $catalogs->links() }}
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

                <div x-show="show" class="relative z-10 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">

                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $isEditMode ? 'Editar Código TUSNE' : 'Registrar Concepto TUSNE' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveTusne">
                        <div class="bg-white px-6 py-6 space-y-6 max-h-[75vh] overflow-y-auto">

                            <!-- Sección 1: Datos Oracle -->
                            <div>
                                <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-database mr-1"></i> Identificadores de Oracle
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Grupo <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="grupo_tusne"
                                            class="w-full px-3 py-2 border @error('grupo_tusne') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Ej: 23" />
                                        @error('grupo_tusne') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Código de Servicio (nu_srvcio) <span class="text-red-500">*</span></label>
                                        <div class="flex gap-2">
                                            <input type="text" wire:model="codigo_tusne"
                                                class="flex-grow px-3 py-2 border @error('codigo_tusne') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                                placeholder="Ej: L0037" />

                                            <button type="button" wire:click="searchOracleTusne" wire:loading.attr="disabled"
                                                class="px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center justify-center focus:outline-none"
                                                title="Buscar y autocompletar desde Oracle">
                                                <i wire:loading.remove wire:target="searchOracleTusne" class="fa-solid fa-magnifying-glass"></i>
                                                <i wire:loading wire:target="searchOracleTusne" class="fa-solid fa-spinner animate-spin"></i>
                                            </button>
                                        </div>
                                        @error('codigo_tusne') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Descripción Oficial de Oracle <span class="text-red-500">*</span></label>
                                        <textarea wire:model="descripcion_local" rows="2"
                                            class="w-full px-3 py-2 border @error('descripcion_local') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Ej: ALQUILER DE CANCHA DE GRASS SINTETICO - PUBLICO EN GENERAL..."></textarea>
                                        @error('descripcion_local') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 2: Discriminadores para el Motor de Selección Automática -->
                            <div>
                                <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-sliders mr-1"></i> Criterios de Aplicación Automática
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Tipo de Espacio -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo de Instalación / Espacio <span class="text-red-500">*</span></label>
                                        <select wire:model="tipo_espacio"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="grass_sintetico">Grass Sintético (Fútbol 7 / 11)</option>
                                            <option value="losa_voley_basquet">Losa de Vóley / Baloncesto</option>
                                            <option value="losa_futsal">Losa de Futsal</option>
                                            <option value="fronton">Cancha de Frontón</option>
                                            <option value="tenis">Cancha de Tenis</option>
                                            <option value="losa_general">Losa Multiuso General</option>
                                            <option value="todos">Cualquier Espacio (General)</option>
                                        </select>
                                    </div>

                                    <!-- Modalidad de Uso -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Finalidad / Tipo de Evento <span class="text-red-500">*</span></label>
                                        <select wire:model="tipo_uso"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="alquiler_regular">Alquiler Regular (Práctica / Pichanga)</option>
                                            <option value="campeonato_corporativo">Campeonato / Evento Corporativo</option>
                                            <option value="liga_oficial">Liga Distrital (Partido Oficial)</option>
                                            <option value="liga_entrenamiento">Liga Distrital (Entrenamiento/Amistoso)</option>
                                            <option value="clase_particular">Clase Particular / Academia</option>
                                            <option value="todos">Cualquier Finalidad</option>
                                        </select>
                                    </div>

                                    <!-- Turno Horario -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Turno Horario <span class="text-red-500">*</span></label>
                                        <select wire:model="horario_turno"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="dia">Diurno (Horario de Día)</option>
                                            <option value="noche">Nocturno (Horario de Noche / Luz)</option>
                                            <option value="madrugada_especial">Madrugada (06:00 AM - 07:00 AM)</option>
                                            <option value="todos">Todo el día (Indiferente)</option>
                                        </select>
                                    </div>

                                    <!-- Tipo de Cliente -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo de Cliente <span class="text-red-500">*</span></label>
                                        <select wire:model="tipo_cliente"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="general">Público en General / Cualquier usuario</option>
                                            <option value="vecino">Vecino Exclusivo (La Molina)</option>
                                            <option value="no_vecino">No Vecino / Visitante</option>
                                            <option value="club_liga">Club Afiliado a Liga Distrital</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 3: Banderas Adicionales -->
                            <div>
                                <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-list-check mr-1"></i> Condiciones de la Reserva
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model="tiene_taquilla" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-medium text-gray-700">Evento con Taquilla / Boletería</span>
                                    </label>

                                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model="incluye_camerinos" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-medium text-gray-700">Incluye Camerinos</span>
                                    </label>

                                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model="incluye_tribunas" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-medium text-gray-700">Incluye Graderías / Tribunas</span>
                                    </label>

                                    <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model="incluye_arcos_f11" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-medium text-gray-700">Estructura Fútbol 11</span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <!-- Botones -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-2">
                            <button type="button" @click="show = false; $wire.closeModal()"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow focus:outline-none transition-colors flex items-center justify-center gap-1.5">
                                <span wire:loading wire:target="saveTusne">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                                <span>{{ $isEditMode ? 'Guardar Cambios' : 'Registrar Concepto' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script SweetAlert2 -->
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