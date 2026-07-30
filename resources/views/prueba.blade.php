<x-app-layout>
    @can('canchas::crear')
    <div x-data="{ open: false }" class="mb-4">
        <button @click="open = !open" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">
            Ver Aviso Alcaldía
        </button>
        <div x-show="open" class="mt-2 text-slate-600 bg-white p-4 rounded border">
            ¡Mantenimiento de canchas este domingo!
        </div>
    </div>
    @endcan

    {{-- @livewire('buscar-canchas') --}}
</x-app-layout>