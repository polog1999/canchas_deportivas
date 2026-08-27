@props([
    'backHref' => null,
    'backLabel' => null,
    'sticky' => true,
    'showSocial' => true,
])

<header {{ $attributes->class([
    'bg-[#1b5e3b] text-white shadow-md z-40',
    'sticky top-0' => $sticky,
]) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('logo_municipal_negro.png') }}" alt="Municipalidad de La Molina"
                class="h-10 w-auto bg-white rounded-md px-2 py-1 object-contain"
                onerror="this.style.display='none'">
            <div class="leading-tight hidden sm:block">
                <p class="text-[10px] uppercase tracking-widest text-emerald-200">Municipalidad de</p>
                <p class="font-bold text-sm">La Molina</p>
            </div>
        </a>

        <div class="flex items-center gap-4 sm:gap-6">
            @if ($showSocial)
                <div class="hidden md:flex items-center gap-3 text-white/90">
                    <a href="#" class="hover:text-white transition" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            @endif

            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap">
                    <i class="fa-regular fa-user"></i>
                    <span class="hidden sm:inline">{{ auth()->user()->loadMissing('perfil')->nombreCompleto() }}</span>
                    <span class="sm:hidden">Cuenta</span>
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap">
                    <i class="fa-regular fa-user"></i>
                    <span>Iniciar sesión / Cuenta</span>
                </a>
            @endauth
        </div>
    </div>
</header>

@if ($backHref || isset($back))
    <div class="bg-white border-b border-emerald-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
            @isset($back)
                {{ $back }}
            @else
                <a href="{{ $backHref }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-[#1b5e3b] hover:text-emerald-800 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    {{ $backLabel ?? 'Volver' }}
                </a>
            @endisset
        </div>
    </div>
@endif
