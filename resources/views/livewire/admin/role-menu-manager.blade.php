<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Roles y Menús</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Roles y Menús</h2>
                    <p class="text-sm text-gray-600">Asignar permisos de menú por rol</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="cargarPermisos"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-rotate"></i> Actualizar
                </button>
                <button type="button" wire:click="openRoleModal"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-plus"></i> Nuevo rol
                </button>
                <button type="button" wire:click="guardarPermisos" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm disabled:opacity-60">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar permisos
                </button>
            </div>
        </div>

        @if ($mensajeExito)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                {{ $mensajeExito }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Rol</label>
                    <select wire:model.live="rolId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}">{{ strtoupper($rol->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($rolActual)
                    <button type="button" wire:click="toggleRolActivo"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-semibold {{ $rolActual->activo ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                        <i class="fa-solid {{ $rolActual->activo ? 'fa-ban' : 'fa-check' }}"></i>
                        {{ $rolActual->activo ? 'Desactivar rol' : 'Activar rol' }}
                    </button>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4 w-16 text-center">Acceso</th>
                            <th class="py-3 px-4">Menú</th>
                            <th class="py-3 px-6">Ruta</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse ($menus as $menu)
                            @php($clave = 'm'.$menu->id)
                            <tr wire:key="menu-permiso-{{ $menu->id }}" class="hover:bg-gray-50/70">
                                <td class="py-3 px-4 text-center align-middle">
                                    @if ($menu->esEnlace())
                                        <input type="checkbox"
                                            id="menu-acceso-{{ $menu->id }}"
                                            wire:key="menu-acceso-{{ $menu->id }}"
                                            wire:model.live="menuAcceso.{{ $clave }}"
                                            class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                    @else
                                        <span class="text-gray-300 text-xs" title="Solo agrupa submenús">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2 {{ $menu->id_padre ? 'pl-4 font-medium text-gray-800' : 'font-bold text-gray-900' }}">
                                        @if ($menu->id_padre)
                                            <span class="text-gray-300 text-xs">└</span>
                                        @endif
                                        <i class="fa-solid {{ $menu->icono ?: ($menu->id_padre ? 'fa-circle' : 'fa-folder') }} text-emerald-600 w-5 text-center"></i>
                                        {{ $menu->nombre }}
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-gray-500 font-mono text-xs align-middle">
                                    {{ $menu->esEnlace() ? $menu->ruta : '---' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-gray-400 italic">
                                    No hay menús registrados. Créalos en Estructura de Menús.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">
                El acceso permite ver el menú en el sidebar y entrar a esa ruta. Puedes cambiar el nombre del menú sin afectar el permiso.
            </div>
        </div>
    </div>

    @if ($isRoleModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" wire:click="closeRoleModal"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-800">Nuevo rol</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" wire:model="nuevoRolNombre"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('nuevoRolNombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" wire:model="nuevoRolDescripcion"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeRoleModal"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" wire:click="crearRol"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                        Crear rol
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        const mostrarSwalRoles = (payload) => {
            const data = payload?.detail?.[0] ?? payload?.detail ?? payload ?? {};
            Swal.fire({
                icon: data.icon || 'success',
                title: data.title || 'Operación exitosa',
                text: data.text || '',
                confirmButtonColor: '#059669',
            });
        };

        window.addEventListener('swal', mostrarSwalRoles);

        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (payload) => mostrarSwalRoles({ detail: [payload] }));
        });
    </script>
</div>
