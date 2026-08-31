@props([
    'title' => 'Reservar más',
    'step' => null,
    'backHref' => null,
    'backLabel' => 'Volver',
    'alpine' => false,
])

<x-app-layout>
    <x-slot name="title">{{ $title }}</x-slot>

    @if ($alpine)
        @once
            @push('scripts')
                <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
            @endpush
            @push('styles')
                <style>[x-cloak]{display:none!important}</style>
            @endpush
        @endonce
    @endif

    <div class="max-w-7xl mx-auto">
        @if ($backHref)
            <a href="{{ $backHref }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-900 mb-5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                {{ $backLabel }}
            </a>
        @endif

        @if ($step)
            <div class="mb-6 flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-xs font-semibold">
                <span @class([
                    'px-3 py-1 rounded-full',
                    'bg-emerald-100 text-emerald-800' => $step >= 1,
                    'text-slate-400' => $step < 1,
                ])>1. Sede y deporte</span>
                <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
                <span @class([
                    'px-3 py-1 rounded-full',
                    'bg-emerald-100 text-emerald-800' => $step >= 2,
                    'text-slate-400' => $step < 2,
                ])>2. Turno</span>
                <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
                <span @class([
                    'px-3 py-1 rounded-full',
                    'bg-emerald-600 text-white' => $step >= 3,
                    'text-slate-400' => $step < 3,
                ])>3. Confirmar y pagar</span>
            </div>
        @endif

        {{ $slot }}
    </div>
</x-app-layout>
