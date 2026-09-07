@props([
    'backHref' => null,
    'backLabel' => null,
    'sticky' => true,
    'showSocial' => true,
])

{{-- =====================================================
     LOADER DEL SISTEMA
===================================================== --}}
<div id="pageLoader"
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-[#1b5e3b] transition-opacity duration-500">
    <div class="flex flex-col items-center justify-center">

        {{-- Balón --}}
        <div class="text-7xl sm:text-8xl text-white animate-spin">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 64 64">
                <path d="M0 0h64v64H0z" fill="none" />
                <circle cx="32" cy="32" r="29.3" fill="#fff" />
                <path fill="#1b5e3b"
                    d="M61.9 32c0-.7.2-10.9-5.8-17.5c-.3-.6-1.5-3-5.6-5.9C47.8 6.5 45 5 44.7 4.8S39.4 2 33.4 2c-.5 0-.9 0-1.4.1c-4.6-.1-8.8 1.1-11.9 2.5c-3.2 1.4-5.3 2.8-5.5 3c-3.4 1.9-9.9 9.5-10.4 13.6c-2.1 2.6-3.8 14.5 0 21.7c2.7 10 12.7 15 13.5 15.4c.5.3 5.9 3.7 12.6 3.7h.9c.6.1 1.1.1 1.7.1c7.2 0 18-5.1 20.2-9.1c6.2-4.6 9.4-16.2 8.8-21M17.8 47.1c-2.9-4.6-4.5-10.7-4.9-12.1c.9-1.4 5.4-8 7.9-10c1.4.3 7.5 1.4 13.2 2.4c.7 1.9 3.9 10 4.8 13.2c-1 1.2-4.9 5.7-8.7 9.2c-4.1.1-11-2.3-12.3-2.7m36-32.5c0 .4-.1 2-.9 3.9c-1.5-.8-5.3-2.4-10.6-2.7c-.8-1.2-3.8-5.3-8.5-8.1c.6-1.3 1.5-2.8 2.1-3.3c.2 0 .4-.1.8-.1c2.5 0 6.9 1.7 7.3 1.8c.4.2 8.3 4.4 9.8 8.5M11.8 34c-3.4-.6-5.5-1.6-6.1-2c-1.3-4.6-.2-9.6-.1-10.3c1.3-2.2 4.8-8 7.2-9.1c2.4-.5 5.5.1 6.7.4c-.1 1.6-.3 6.1.3 10.9c-2.6 2.2-6.9 8.5-8 10.1M31.7 3.5c.8.1 1.9.2 2.7.5c-.8 1-1.6 2.5-1.9 3.3c-1.6.3-7.5 1.4-12.2 4.4c-.9-.2-3.8-.9-6.5-.7c.7-1.3 1.7-2.2 1.8-2.3c.3-.3 7.4-5.3 16.1-5.2m19.1 38.1c-1.2 0-5.7-.3-10.6-1.5c-.9-3.3-4.1-11.4-4.8-13.3c3.1-4.4 6.1-8.5 6.9-9.7c5.7.4 9.7 2.5 10.5 2.9c3.3 5.3 4 10.7 4.1 11.6c-1.8 5.5-5.2 9.2-6.1 10M3.7 28.5c.1 1.3.3 2.6.7 3.9c-.3.9-.6 1.8-.7 2.7c-.3-2.3-.3-4.6 0-6.6M18.5 57l-.4.6zc-2.5-1.2-4.4-4-5.2-5.1c1.5-1.5 3.4-2.9 4.1-3.4c1.6.6 8.3 2.8 12.6 2.8c.7 1 3.1 4 6 6.4c-1.8 1.8-4.4 2.6-4.9 2.8c-6.8.2-12.6-3.5-12.6-3.5m16.3 3.4c.9-.5 1.9-1.2 2.7-2.1c1.3-.2 6.9-1.1 11.9-4.8c.3 0 .9.1 1.5.1c-3.1 2.9-10.5 6.2-16.1 6.8M50.2 52c1.8-4.7 1.7-8.3 1.6-9.4c1-1 4.4-4.6 6.3-10.1c1 .2 1.7.4 2 .6c.1.4.3 1.3.2 2.7c-.8 5-3.4 12.6-8.1 15.9c-.5.3-1.3.4-2 .3" />
            </svg>


        </div>

        {{-- Nombre --}}
        <h2 class="mt-6 text-xl sm:text-2xl font-bold text-white tracking-wide">
            Canchas Deportivas
        </h2>

        {{-- Estado --}}
        <p class="mt-2 text-sm text-white/70">
            Cargando...
        </p>

        {{-- Barra --}}
        <div class="mt-5 w-32 h-1.5 bg-white/20 rounded-full overflow-hidden">
            <div class="h-full w-1/2 bg-white rounded-full animate-pulse"></div>
        </div>

    </div>
</div>

<header {{ $attributes->class(['bg-[#1b5e3b] text-white shadow-md z-40', 'sticky top-0' => $sticky]) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">

        {{-- LOGO + NOMBRE DEL SISTEMA --}}
        <a href="{{ url('/') }}" class="flex items-center gap-4 min-w-0">

            {{-- Logo --}}
            <div class="flex items-center justify-center shrink-0">
                <img src="{{ asset('logo_municipal_negro2.png') }}" alt="Municipalidad de La Molina"
                    class="h-[60px] w-auto rounded-lg object-contain" onerror="this.style.display='none'">
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

                    <a href="https://www.tiktok.com/@munilamolina" class="hover:text-white transition" target="_blank"
                        aria-label="TikTok">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>

                    <a href="https://www.facebook.com/MunicipalidadDeLaMolina/" class="hover:text-white transition"
                        target="_blank" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="https://www.instagram.com/munilamolina/" class="hover:text-white transition"
                        target="_blank" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a href="https://www.youtube.com/@munidelamolina" class="hover:text-white transition"
                        target="_blank" aria-label="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>

                </div>
            @endif


            {{-- USUARIO AUTENTICADO --}}
            @auth
                @php
                    $nombreMostrar = auth()->user()->loadMissing('perfil')->nombreParaMostrar();
                @endphp

                <details class="relative group" data-user-menu>

                    <summary
                        class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden">

                        <i class="fa-regular fa-user"></i>

                        <span class="max-w-[12rem] truncate">
                            {{ $nombreMostrar }}
                        </span>

                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform group-open:rotate-180"></i>

                    </summary>


                    {{-- DROPDOWN --}}
                    <div
                        class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white text-slate-800 shadow-xl border border-slate-200/80 py-1 z-50">

                        <div class="px-4 py-3 border-b border-slate-100">

                            <p class="text-sm font-semibold truncate">
                                {{ $nombreMostrar }}
                            </p>

                        </div>


                        {{-- Ir al portal --}}
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-slate-50 transition">

                            <i class="fa-solid fa-gauge-high w-4 text-emerald-700"></i>

                            Ir al portal

                        </a>


                        {{-- Cerrar sesión --}}
                        <form action="{{ route('logout') }}" method="POST">

                            @csrf

                            <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">

                                <i class="fa-solid fa-right-from-bracket w-4"></i>

                                Cerrar sesión

                            </button>

                        </form>

                    </div>

                </details>
            @else
                {{-- USUARIO NO AUTENTICADO --}}
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap">

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
@once
    <script>
        window.addEventListener('load', function() {

            const loader = document.getElementById('pageLoader');

            if (!loader) return;

            loader.classList.add('opacity-0');

            setTimeout(() => {
                loader.remove();
            }, 500);

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
                <a href="{{ $backHref }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-[#1b5e3b] hover:text-emerald-800 transition">

                    <i class="fa-solid fa-arrow-left"></i>

                    {{ $backLabel ?? 'Volver' }}

                </a>
            @endisset

        </div>

    </div>
@endif
