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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">

    {{-- LOGO + NOMBRE DEL SISTEMA --}}
    <a href="{{ url('/') }}" class="flex items-center gap-4 min-w-0">

        {{-- Logo --}}
        <div class="flex items-center justify-center shrink-0">
            <img
                src="{{ asset('logo_municipal_negro.png') }}"
                alt="Municipalidad de La Molina"
                class="h-[60px] w-auto rounded-lg object-contain"
                onerror="this.style.display='none'"
            >
        </div>

        {{-- Separador + nombre --}}
        <div class="flex items-center gap-4 min-w-0">

            <div class="h-12 w-[2px] bg-white/70"></div>

            <div class="leading-tight">
                <p class="text-base sm:text-lg font-bold tracking-tight text-white whitespace-nowrap">
                    Canchas Deportivas
                </p>
            </div>

        </div>
    </a>


    {{-- PARTE DERECHA --}}
    <div class="flex items-center gap-4 sm:gap-6">

        @if ($showSocial)
            <div class="hidden md:flex items-center gap-3 text-white/90">

                <a
                    href="#"
                    class="hover:text-white transition"
                    aria-label="TikTok"
                >
                    <i class="fa-brands fa-tiktok"></i>
                </a>

                <a
                    href="#"
                    class="hover:text-white transition"
                    aria-label="Facebook"
                >
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a
                    href="#"
                    class="hover:text-white transition"
                    aria-label="Instagram"
                >
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a
                    href="#"
                    class="hover:text-white transition"
                    aria-label="YouTube"
                >
                    <i class="fa-brands fa-youtube"></i>
                </a>

            </div>
        @endif


        {{-- USUARIO AUTENTICADO --}}
        @auth
            @php
                $nombreMostrar = auth()->user()
                    ->loadMissing('perfil')
                    ->nombreParaMostrar();
            @endphp

            <details
                class="relative group"
                data-user-menu
            >

                <summary
                    class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden"
                >

                    <i class="fa-regular fa-user"></i>

                    <span class="max-w-[12rem] truncate">
                        {{ $nombreMostrar }}
                    </span>

                    <i
                        class="fa-solid fa-chevron-down text-[10px] transition-transform group-open:rotate-180"
                    ></i>

                </summary>


                {{-- DROPDOWN --}}
                <div
                    class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white text-slate-800 shadow-xl border border-slate-200/80 py-1 z-50"
                >

                    <div class="px-4 py-3 border-b border-slate-100">

                        <p class="text-sm font-semibold truncate">
                            {{ $nombreMostrar }}
                        </p>

                    </div>


                    {{-- Ir al portal --}}
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-slate-50 transition"
                    >

                        <i class="fa-solid fa-gauge-high w-4 text-emerald-700"></i>

                        Ir al portal

                    </a>


                    {{-- Cerrar sesión --}}
                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"
                        >

                            <i class="fa-solid fa-right-from-bracket w-4"></i>

                            Cerrar sesión

                        </button>

                    </form>

                </div>

            </details>

        @else

            {{-- USUARIO NO AUTENTICADO --}}
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap"
            >

                <i class="fa-regular fa-user"></i>

                <span>
                    Iniciar sesión / Cuenta
                </span>

            </a>

        @endauth

    </div>

</div>
</header>


@once
    <script>
        document.addEventListener('click', (event) => {

            document
                .querySelectorAll('details[data-user-menu][open]')
                .forEach((menu) => {

                    if (!menu.contains(event.target)) {
                        menu.removeAttribute('open');
                    }

                });

        });
    </script>
@endonce


{{-- BARRA DE RETORNO --}}
@if ($backHref || isset($back))

    <div class="bg-white border-b border-emerald-100">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">

            @isset($back)

                {{ $back }}

            @else

                <a
                    href="{{ $backHref }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-[#1b5e3b] hover:text-emerald-800 transition"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    {{ $backLabel ?? 'Volver' }}

                </a>

            @endisset

        </div>

    </div>

@endif