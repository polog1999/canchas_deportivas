<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Gestión de Usuarios</x-slot>
    <div class="max-w-7xl mx-auto">
        <!-- Encabezado de la Sección -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Control de Usuarios</h2>
                <p class="text-sm text-gray-600">Administra las credenciales de acceso y la información del perfil de los
                    usuarios.</p>
            </div>

            <button wire:click="openModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors duration-200">
                <i class="fa-solid fa-user-plus"></i>
                Nuevo Usuario
            </button>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live="search" type="text" placeholder="Buscar por nombre, email o DNI..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
            <div wire:loading class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> Procesando búsqueda...
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Usuario / Cuenta</th>
                            <th class="py-4 px-6">Identificación</th>
                            <th class="py-4 px-6">Nombres Completos</th>
                             <th class="py-4 px-6">Rol</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-gray-900">{{ $user->usuario }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->correo_electronico }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->perfil && $user->perfil->numero_documento)
                                        <span class="bg-gray-100 text-gray-800 text-xs px-2.5 py-1 rounded font-medium">
                                            {{ $user->perfil->tipo_documento->value ?? 'DOC' }}:
                                            {{ $user->perfil->numero_documento }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Sin documento</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->perfil && $user->perfil->nombreCompleto() !== '')
                                        <div class="font-medium text-gray-800">
                                            {{ $user->perfil->nombreCompleto() }}
                                        </div>
                                        <div class="text-xs text-gray-400 truncate max-w-xs">
                                            {{ $user->perfil->direccion ?? 'Sin dirección' }}</div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Perfil incompleto</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->rol)
                                        <span class="bg-gray-100 text-gray-800 text-xs px-2.5 py-1 rounded font-medium">
                                            {{ $user->rol->nombre }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Sin Rol</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <button wire:click="toggleStatus({{ $user->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $user->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <span
                                            class="w-2 h-2 rounded-full mr-1.5 {{ $user->activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                        {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex item-center justify-center gap-2">
                                        <button wire:click="editUser({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                            title="Editar Usuario">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="toggleStatus({{ $user->id }})"
                                            class="w-8 h-8 rounded-lg {{ $user->activo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} flex items-center justify-center transition-colors"
                                            title="{{ $user->activo ? 'Desactivar' : 'Activar' }}">
                                            <i
                                                class="fa-solid {{ $user->activo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-regular fa-folder-open text-3xl text-gray-300"></i>
                                        <span>No se encontraron registros de usuarios disponibles.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Modal de Creación / Edición (Controlado por AlpineJS y Livewire) -->
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
                            {{ $isEditMode ? 'Editar Usuario y Perfil' : 'Registrar Nuevo Usuario' }}
                        </h3>
                        <button type="button" @click="show = false; $wire.closeModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Formulario de Registro / Edición -->
                    <form wire:submit.prevent="saveUser">
                        <div class="bg-white px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto">

                            <!-- Sección 1: Datos de la Cuenta -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-key mr-1"></i> Credenciales de Acceso
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre de Usuario
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="usuario"
                                            class="w-full px-3 py-2 border @error('usuario') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Ej: jgomez" />
                                        @error('usuario')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Correo
                                            Electrónico</label>
                                        <input type="email" wire:model="correo_electronico"
                                            class="w-full px-3 py-2 border @error('correo_electronico') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="correo@ejemplo.com" />
                                        @error('correo_electronico')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                                            Contraseña
                                            @if (!$isEditMode)
                                                <span class="text-red-500">*</span>
                                            @else
                                                <span class="text-gray-400 font-normal">(dejar en blanco para
                                                    conservar)</span>
                                            @endif
                                        </label>
                                        <input type="password" wire:model="clave"
                                            class="w-full px-3 py-2 border @error('clave') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="••••••••" />
                                        @error('clave')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                                        <select wire:model.defer="rol_id"
                                            class="w-full px-3 py-2 border @error('rol_id') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="">Selecciona un rol...</option>
                                            @foreach ($roles as $rol)
                                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('rol_id')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Estado de la
                                            Cuenta</label>
                                        <select wire:model="activo"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="1">Activo / Permitir Acceso</option>
                                            <option value="0">Inactivo / Bloquear Acceso</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 2: Información del Perfil -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-address-card mr-1"></i> Información del Perfil Personal
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo
                                            Documento</label>
                                        <select wire:model.defer="tipo_documento"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="">Seleccione...</option>
                                            @foreach (App\Enums\DocumentType::cases() as $typeDocument)
                                                <option value="{{ $typeDocument->value }}">{{ $typeDocument->value }}
                                                </option>
                                            @endforeach
                                            {{-- <option value="DNI">DNI</option>
                                            <option value="CEX">Carnet Extranjería</option>
                                            <option value="PAS">Pasaporte</option> --}}
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Número
                                            Documento</label>
                                        <input type="text" wire:model.defer="numero_documento"
                                            class="w-full px-3 py-2 border @error('numero_documento') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="N° de Identidad" />
                                        @error('numero_documento')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="nombres"
                                            class="w-full px-3 py-2 border @error('nombres') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Nombres" />
                                        @error('nombres')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Apellido
                                            Paterno <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="apellido_paterno"
                                            class="w-full px-3 py-2 border @error('apellido_paterno') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Paterno" />
                                        @error('apellido_paterno')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Apellido
                                            Materno <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="apellido_materno"
                                            class="w-full px-3 py-2 border @error('apellido_materno') border-red-500 @else border-gray-300 @enderror rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Materno" />
                                        @error('apellido_materno')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Dirección
                                            Física</label>
                                        <input type="text" wire:model.defer="direccion"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Av, Calle, N° o Dpto..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Sección 3: Ubigeo -->
                            <div>
                                <h4
                                    class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-3 border-b border-emerald-100 pb-1">
                                    <i class="fa-solid fa-map-location-dot mr-1"></i> Ubicación Geográfica (Ubigeo)
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-700 mb-1">Departamento</label>
                                        <input type="text" wire:model.defer="ubigeo_departamento"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Ej: Lima" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Provincia</label>
                                        <input type="text" wire:model.defer="ubigeo_provincia"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500"
                                            placeholder="Ej: Lima" />
                                    </div> --}}

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Distrito</label>
                                        <select wire:model.defer="ubigeo_distrito"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                            <option value="">Seleccione...</option>
                                            @php
                                             $servicio = app(App\Services\OracleService::class);
                                             $distritos = $servicio->getDistritos();
                                            @endphp
                                            @foreach ($distritos as $distrito)
                                                <option value="{{ $distrito->districodi }}">{{ $distrito->distridesc }}
                                                </option>
                                            @endforeach
                                            {{-- <option value="DNI">DNI</option>
                                            <option value="CEX">Carnet Extranjería</option>
                                            <option value="PAS">Pasaporte</option> --}}
                                        </select>
                                    </div>

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
                                <span wire:loading wire:target="saveUser">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                </span>
                                <span>{{ $isEditMode ? 'Guardar Cambios' : 'Registrar' }}</span>
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
                // Compatible con formatos de Livewire v2 y v3
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
