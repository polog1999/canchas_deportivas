@props(['menu', 'esRaiz' => false])

<div class="menu-sortable-item {{ $esRaiz ? 'bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden' : 'bg-white rounded-lg border border-gray-100' }}"
    data-id="{{ $menu->id }}"
    data-has-children="{{ $menu->hijos->isNotEmpty() ? '1' : '0' }}">

    <div class="{{ $esRaiz ? 'p-4' : 'p-3' }} flex items-start gap-3">
        <div class="drag-handle pt-1 px-1 text-gray-400 hover:text-emerald-600 cursor-grab active:cursor-grabbing"
            title="Arrastrar">
            <i class="fa-solid fa-grip-vertical"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="{{ $esRaiz ? 'font-bold text-gray-900' : 'font-semibold text-gray-800' }}">{{ $menu->nombre }}</span>
                <span class="text-xs text-gray-400">#{{ $menu->id }}</span>
                @unless ($menu->activo)
                    <span class="text-[10px] uppercase font-bold tracking-wide px-2 py-0.5 rounded bg-red-50 text-red-600">Inactivo</span>
                @endunless
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $menu->esEnlace() ? $menu->ruta : 'Sin ruta (carpeta)' }}
                · orden {{ $menu->orden }}
                · <i class="fa-solid {{ $menu->icono ?: ($esRaiz ? 'fa-folder' : 'fa-circle') }}"></i>
                {{ $menu->icono ?: ($esRaiz ? 'fa-folder' : 'fa-circle') }}
            </p>
        </div>

        <div class="flex items-center gap-1 shrink-0" onclick="event.stopPropagation()">
            @if ($esRaiz)
                <button type="button" wire:click="openModal({{ $menu->id }})"
                    class="w-8 h-8 rounded-lg text-sky-600 hover:bg-sky-50" title="Agregar submenú">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
            @endif
            <button type="button" wire:click="editMenu({{ $menu->id }})"
                class="w-8 h-8 rounded-lg text-emerald-600 hover:bg-emerald-50" title="Editar">
                <i class="fa-solid fa-pen text-xs"></i>
            </button>
            <button type="button" wire:click="toggleStatus({{ $menu->id }})"
                class="w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50" title="Activar/Desactivar">
                <i class="fa-solid fa-power-off text-xs"></i>
            </button>
            <button type="button" wire:click="deleteMenu({{ $menu->id }})"
                wire:confirm="¿Eliminar este menú?"
                class="w-8 h-8 rounded-lg text-red-600 hover:bg-red-50" title="Eliminar">
                <i class="fa-solid fa-trash text-xs"></i>
            </button>
        </div>
    </div>

    @if ($esRaiz)
        <div class="menu-sortable-children border-t border-gray-100 bg-gray-50/60 pl-8 pr-4 py-2 space-y-2 min-h-[2.5rem]">
            @foreach ($menu->hijos as $hijo)
                @include('livewire.admin.partials.menu-sortable-item', ['menu' => $hijo, 'esRaiz' => false])
            @endforeach
        </div>
    @endif
</div>
