<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Configuración de Catálogo TUSNE</x-slot>
    <div class="max-w-7xl mx-auto">

        <!-- Encabezado de la Sección -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Catálogo de Conceptos TUSNE</h2>
                <p class="text-sm text-gray-600">Configura y vincula los códigos arancelarios TUSNE de Oracle con los
                    servicios de reserva web.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-plus-circle"></i>
                Nuevo TUSNE
            </button>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por descripción, grupo o código..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Consultando registros...
            </div>
        </div>

        <!-- Tabla de Conceptos TUSNE -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Identificadores Oracle</th>
                            <th class="py-4 px-6">Descripción Local</th>
                            <th class="py-4 px-6">Atributos Adicionales</th>
                            <th class="py-4 px-6">Modo / Tarifa</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($catalogs as $catalog)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="bg-emerald-50 text-emerald-800 text-xs px-2.5 py-1 rounded-md font-bold border border-emerald-100">
                                            G: {{ $catalog->grupo_tusne }}
                                        </span>
                                        <span
                                            class="bg-slate-100 text-slate-800 text-xs px-2.5 py-1 rounded-md font-bold border border-slate-200">
                                            Cod: {{ $catalog->codigo_tusne }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-gray-900 max-w-xs md:max-w-md truncate"
                                        title="{{ $catalog->descripcion_local }}">
                                        {{ $catalog->descripcion_local }}
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1">
                                        @if ($catalog->incluye_camerinos)
                                            <span
                                                class="bg-blue-50 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-medium border border-blue-100">Camerino</span>
                                        @endif
                                        @if ($catalog->incluye_tribunas)
                                            <span
                                                class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-medium border border-indigo-100">Tribuna</span>
                                        @endif
                                        @if ($catalog->incluye_arcos_f11)
                                            <span
                                                class="bg-purple-50 text-purple-700 text-[10px] px-2 py-0.5 rounded-full font-medium border border-purple-100">Fútbol
                                                11</span>
                                        @endif
                                        @if ($catalog->tiene_recaudacion_taquilla)
                                            <span
                                                class="bg-amber-50 text-amber-700 text-[10px] px-2 py-0.5 rounded-full font-medium border border-amber-100">Taquilla
                                                / Boleto</span>
                                        @endif
                                        @if (
                                            !$catalog->incluye_camerinos &&
                                                !$catalog->incluye_tribunas &&
                                                !$catalog->incluye_arcos_f11 &&
                                                !$catalog->tiene_recaudacion_taquilla)
                                            <span class="text-gray-400 italic text-xs">Sin agregados</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-xs space-y-1">
                                        <div><span class="font-semibold text-gray-500">Horario:</span>
                                            {{ $catalog->modificador_tiempo === 'dia' ? 'Diurno' : ($catalog->modificador_tiempo === 'noche' ? 'Nocturno' : 'Normal') }}
                                        </div>
                                        <div><span class="font-semibold text-gray-500">Público:</span>
                                            {{ $catalog->tipo_cliente === 'vecino' ? 'Vecino de La Molina' : ($catalog->tipo_cliente === 'no_vecino' ? 'No Vecino' : 'General') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleStatus({{ $catalog->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $catalog->esta_activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <span
                                            class="w-2 h-2 rounded-full mr-1.5 {{ $catalog->esta_activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
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
                                            <i
                                                class="fa-solid {{ $catalog->esta_activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-regular fa-folder-open text-3xl text-gray-300"></i>
                                        <span>No se encontraron registros TUSNE disponibles.</span>
                                    </div>
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

                <!-- Fondo traslúcido -->
                <div class="fixed inset-0 transition-opacity" aria-hidden="true"
                    @click="show = false; $wire.closeModal()">
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
                    class="relative z-10 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">

                    <!-- Cabecera del Modal -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $isEditMode ? 'Editar Código TUSNE' : 'Registrar Nuevo Concepto TUSNE' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Formulario de Registro / Edición -->
                    <form wire:submit.prevent="saveTusne">
                        <div class="bg-white px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto">

                            <!-- Sección 1: Datos Generales Oracle -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-database mr-1"></i> Identificadores de Estructura Oracle
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Código de Grupo
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="grupo_tusne"
                                            class="w-full px-3 py-2 border @error('grupo_tusne') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Ej: 23" disabled/>
                                        @error('grupo_tusne')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Código de
                                            Servicio <span class="text-red-500">*</span></label>
                                        <div class="flex gap-2">
                                            <input type="text" wire:model="codigo_tusne"
                                                class="flex-grow px-3 py-2 border @error('codigo_tusne') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                                placeholder="Ej: 0125" />

                                            <!-- Botón Lupa de Consulta en Oracle -->
                                            <button type="button" wire:click="searchOracleTusne"
                                                wire:loading.attr="disabled"
                                                class="px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center justify-center focus:outline-none"
                                                title="Buscar descripción en Oracle">

                                                <i wire:loading.remove wire:target="searchOracleTusne"
                                                    class="fa-solid fa-magnifying-glass"></i>
                                                <i wire:loading wire:target="searchOracleTusne"
                                                    class="fa-solid fa-spinner animate-spin"></i>
                                            </button>
                                        </div>
                                        @error('codigo_tusne')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Descripción Local <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="descripcion_local"
                                            class="w-full px-3 py-2 border @error('descripcion_local') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Ej: Cancha Sintética Fútbol 11 - Turno Noche con Camerino" disabled />
                                        @error('descripcion_local')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 2: Modificadores y Clasificaciones -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-sliders mr-1"></i> Modificadores de Venta
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Modificador
                                            Horario</label>
                                        <select wire:model="modificador_tiempo"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="ninguno">Normal / Sin Modificador</option>
                                            <option value="dia">Diurno (Luz Natural)</option>
                                            <option value="noche">Nocturno (Luz Artificial)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo de
                                            Cliente</label>
                                        <select wire:model="tipo_cliente"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="general">Público General</option>
                                            <option value="vecino">Vecino Exclusivo (La Molina)</option>
                                            <option value="no_vecino">No Vecinos / Visitantes</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Habilitar
                                            Concepto</label>
                                        <select wire:model="esta_activo"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="1">Activo (Visible para Reservas)</option>
                                            <option value="0">Inactivo (Ocultar)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 3: Atributos de Reserva (Banderas lógicas) -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-list-check mr-1"></i> Paquete e Infraestructura Incluida
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <!-- Camerino -->
                                    <label
                                        class="relative flex items-start p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="incluye_camerinos"
                                                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                        </div>
                                        <div class="ml-3 text-xs">
                                            <span class="font-bold text-gray-800 block">Incluye Camerinos</span>
                                            <span class="text-gray-500">Habilita acceso a vestidores de la sede</span>
                                        </div>
                                    </label>

                                    <!-- Tribuna -->
                                    <label
                                        class="relative flex items-start p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="incluye_tribunas"
                                                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                        </div>
                                        <div class="ml-3 text-xs">
                                            <span class="font-bold text-gray-800 block">Incluye Tribuna</span>
                                            <span class="text-gray-500">Permite graderías para espectadores</span>
                                        </div>
                                    </label>

                                    <!-- Fútbol 11 -->
                                    <label
                                        class="relative flex items-start p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="incluye_arcos_f11"
                                                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                        </div>
                                        <div class="ml-3 text-xs">
                                            <span class="font-bold text-gray-800 block">Estructura Fútbol 11</span>
                                            <span class="text-gray-500">Contempla arcos y pintado reglamentario</span>
                                        </div>
                                    </label>

                                    <!-- Cobro Taquilla -->
                                    <label
                                        class="relative flex items-start p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="tiene_recaudacion_taquilla"
                                                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                        </div>
                                        <div class="ml-3 text-xs">
                                            <span class="font-bold text-gray-800 block">Cobro de Taquilla</span>
                                            <span class="text-gray-500">Habilitado para eventos con fines
                                                lucrativos</span>
                                        </div>
                                    </label>

                                </div>
                            </div>

                        </div>

                        <!-- Botones de Acción -->
                        <div
                            class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-2">
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

    <!-- Integración con SweetAlert2 para Alertas Interactivas -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('swal', event => {
                const payload = event.detail[0] || event.detail;
                Swal.fire({
                    icon: payload.icon || 'success',
                    title: payload.title || 'Operación Exitosa',
                    text: payload.text || '',
                    confirmButtonColor: '#059669', // Emerald 600
                });
            });
        });
    </script>
</div>
