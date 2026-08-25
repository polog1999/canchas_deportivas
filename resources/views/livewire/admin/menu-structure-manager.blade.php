<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Estructura de Menús</x-slot>

    @push('styles')
        <style>
            .menu-sortable-root .sortable-ghost,
            .menu-sortable-children .sortable-ghost {
                opacity: 0.45;
                background: #ecfdf5 !important;
                border-color: #6ee7b7 !important;
            }

            .menu-sortable-root .sortable-drag,
            .menu-sortable-children .sortable-drag {
                box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.15);
            }

            .menu-sortable-children.sortable-drag-over {
                background: #f0fdf4;
                outline: 2px dashed #34d399;
                outline-offset: -2px;
                border-radius: 0.75rem;
            }
        </style>
    @endpush

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Estructura de Menús</h2>
                <p class="text-sm text-gray-600">Arrastra las piezas para reordenar menús y submenús</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="$refresh"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-rotate"></i> Actualizar
                </button>
                <button type="button" wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm">
                    <i class="fa-solid fa-plus"></i> Menú raíz
                </button>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2">
            <i class="fa-solid fa-hand-pointer"></i>
            Usa el icono <i class="fa-solid fa-grip-vertical mx-1"></i> para jalar y soltar. Suelta un ítem dentro de una carpeta para convertirlo en submenú.
        </div>

        <div wire:key="menu-tree-{{ $raices->pluck('id')->join('-') }}-{{ $raices->sum(fn ($m) => $m->hijos->count()) }}"
            id="menu-sortable-root"
            class="menu-sortable-root space-y-3">
            @forelse ($raices as $menu)
                @include('livewire.admin.partials.menu-sortable-item', ['menu' => $menu, 'esRaiz' => true])
            @empty
                <div class="bg-white rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-400">
                    No hay menús. Crea el primero con el botón <strong>+ Menú raíz</strong>.
                </div>
            @endforelse
        </div>
    </div>

    @if ($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-800">
                    {{ $isEditMode ? 'Editar menú' : ($id_padre ? 'Nuevo submenú' : 'Nuevo menú raíz') }}
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Menú padre</label>
                        <select wire:model="id_padre"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Ninguno (raíz) —</option>
                            @foreach ($padresDisponibles as $padre)
                                <option value="{{ $padre->id }}">{{ $padre->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_padre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" wire:model="nombre"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ej. Roles y Menús">
                        @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ruta (link)</label>
                        <input type="text" wire:model="ruta"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono"
                            placeholder="/portal/roles-menus">
                        <p class="text-[11px] text-gray-400 mt-1">Usa <code>#</code> si es solo carpeta sin enlace.</p>
                        @error('ruta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icono (Font Awesome)</label>
                        <input type="text" wire:model="icono"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="fa-shield">
                        @error('icono') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                        <input type="number" wire:model="orden" min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('orden') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="activo"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            Activo
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" wire:click="saveMenu"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            (function () {
                let sortableInstances = [];
                let saving = false;

                function destroySortables() {
                    sortableInstances.forEach(instance => instance.destroy());
                    sortableInstances = [];
                }

                function serializeMenuTree(rootEl) {
                    const items = [];

                    function walk(container, parentId) {
                        Array.from(container.children)
                            .filter(el => el.classList.contains('menu-sortable-item'))
                            .forEach((el, index) => {
                                const id = parseInt(el.dataset.id, 10);
                                items.push({
                                    id,
                                    id_padre: parentId,
                                    orden: index + 1,
                                });

                                const nested = el.querySelector(':scope > .menu-sortable-children');
                                if (nested) {
                                    walk(nested, id);
                                }
                            });
                    }

                    walk(rootEl, null);
                    return items;
                }

                function initMenuSortables() {
                    destroySortables();

                    const root = document.getElementById('menu-sortable-root');
                    if (!root || typeof Sortable === 'undefined') {
                        return;
                    }

                    const containers = [root, ...root.querySelectorAll('.menu-sortable-children')];

                    containers.forEach(container => {
                        sortableInstances.push(new Sortable(container, {
                            group: 'menu-nested',
                            animation: 180,
                            handle: '.drag-handle',
                            draggable: '.menu-sortable-item',
                            fallbackOnBody: true,
                            swapThreshold: 0.65,
                            ghostClass: 'sortable-ghost',
                            dragClass: 'sortable-drag',
                            onMove(evt) {
                                const dragged = evt.dragged;
                                const hasChildren = dragged.dataset.hasChildren === '1';
                                const targetList = evt.to;
                                const isRootList = targetList.classList.contains('menu-sortable-root');

                                if (dragged.contains(targetList)) {
                                    return false;
                                }

                                if (hasChildren && !isRootList) {
                                    return false;
                                }

                                return true;
                            },
                            onEnd() {
                                if (saving) {
                                    return;
                                }

                                const payload = serializeMenuTree(root);
                                const componentEl = root.closest('[wire\\:id]');

                                if (!componentEl || !window.Livewire) {
                                    return;
                                }

                                saving = true;
                                window.Livewire.find(componentEl.getAttribute('wire:id'))
                                    .call('reordenarMenus', payload)
                                    .catch(() => {
                                        window.location.reload();
                                    })
                                    .finally(() => {
                                        saving = false;
                                    });
                            },
                        }));
                    });
                }

                document.addEventListener('DOMContentLoaded', initMenuSortables);

                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', ({ el }) => {
                        if (el.querySelector && el.querySelector('#menu-sortable-root')) {
                            initMenuSortables();
                        }
                    });
                });
            })();
        </script>
    @endpush
</div>
