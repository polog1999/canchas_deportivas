<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>
        Portal
        {{ auth()->user()->rol?->nombre === 'admin' ? 'Administrador' : auth()->user()->rol?->nombre ?? 'Usuario' }}
        | {{ $title ?? 'Alquiler de Canchas' }}
    </title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @livewireStyles
</head>

<body class="bg-slate-100 text-slate-800 font-sans antialiased overflow-x-hidden">

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
<div class="relative min-h-screen flex">

    {{-- =====================================================
         OVERLAY MÓVIL
    ====================================================== --}}
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-slate-900/50 z-40
               transition-opacity duration-300
               opacity-0 pointer-events-none lg:hidden">
    </div>


    {{-- =====================================================
         SIDEBAR
         
         IMPORTANTE:
         Ya NO usamos -translate-x-full ni lg:translate-x-0
         aquí. El movimiento será controlado completamente
         desde JavaScript para evitar conflictos en móvil.
    ====================================================== --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-[50]
               w-64
               bg-slate-900
               text-slate-300
               flex flex-col
               border-r border-slate-800
               overflow-visible
               transition-transform duration-300 ease-in-out">

        {{-- =================================================
             CABECERA
        ================================================== --}}
        <a href="/">
        <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">

            <img src="{{ asset('favicon.png') }}"
                class="w-10 h-auto"
                alt="Logo La Molina">

            <div>
                <h3 class="text-xs font-bold tracking-wider text-emerald-500 uppercase">
                    Canchas Deportivas
                </h3>

                <p class="text-[10px] text-slate-500 font-semibold tracking-tight uppercase">
                    La Molina
                </p>
            </div>

        </div>
</a>

        {{-- =================================================
             MENÚ
        ================================================== --}}
        <div class="flex-grow overflow-y-auto px-4 py-6 space-y-6">

            <ul class="space-y-1">

                @php
                    $menusUsuario = auth()->user()->menusArbol();
                @endphp

                @if ($menusUsuario->isNotEmpty())

                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        Gestión Municipal
                    </div>

                    @foreach ($menusUsuario as $menu)

                        @if ($menu->hijos->isNotEmpty())

                            <div class="px-4 py-2 mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">

                                <i class="fa-solid {{ $menu->icono ?: 'fa-folder' }} mr-1 text-emerald-600"></i>

                                {{ $menu->nombre }}

                            </div>


                            @foreach ($menu->hijos as $hijo)

                                @if ($hijo->esEnlace())

                                    <li>

                                        <a href="{{ $hijo->url() }}"
                                            class="flex items-center gap-3 px-4 py-2.5 pl-6 text-sm font-medium rounded-lg transition-colors {{ $hijo->estaActivo() ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">

                                            <i class="fa-solid {{ $hijo->icono ?: 'fa-circle' }} w-5 text-center text-emerald-500"></i>

                                            {{ $hijo->nombre }}

                                        </a>

                                    </li>

                                @endif

                            @endforeach


                        @elseif ($menu->esEnlace())

                            <li>

                                <a href="{{ $menu->url() }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $menu->estaActivo() ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">

                                    <i class="fa-solid {{ $menu->icono ?: 'fa-circle' }} w-5 text-center text-emerald-500"></i>

                                    {{ $menu->nombre }}

                                </a>

                            </li>


                        @else

                            <div class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">

                                {{ $menu->nombre }}

                            </div>

                        @endif

                    @endforeach

                @endif

            </ul>

        </div>


        {{-- =================================================
             CERRAR SESIÓN
        ================================================== --}}
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-2.5 px-4 text-sm font-semibold rounded-lg bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white transition-colors duration-150">

                    <i class="fa-solid fa-power-off"></i>

                    Cerrar sesión

                </button>

            </form>

        </div>

    </aside>


    {{-- =====================================================
         FLECHA PARA DESPLEGAR / OCULTAR
         
         IMPORTANTE:
         Está COMPLETAMENTE FUERA del sidebar.
         
         Esto hace que:
         - En escritorio quede exactamente en el borde.
         - En móvil siga visible cuando el sidebar está cerrado.
         - No desaparezca junto con el sidebar.
    ====================================================== --}}
    <button id="sidebarToggleBtn"
        type="button"
        aria-label="Ocultar menú"
        aria-expanded="true"
        class="fixed top-1/2 -translate-y-1/2
               z-[9999]
               w-8 h-14
               flex items-center justify-center
               bg-slate-900
               border border-slate-700
               rounded-r-xl
               shadow-xl
               text-slate-300
               hover:bg-slate-800
               hover:text-white
               transition-all duration-300
               focus:outline-none">

        <i id="sidebarToggleIcon"
            class="fa-solid fa-chevron-left text-xs">
        </i>

    </button>


    {{-- =====================================================
         CONTENIDO PRINCIPAL
    ====================================================== --}}
    <div id="mainWrapper"
        class="flex-grow flex flex-col min-w-0 w-full transition-all duration-300 ease-in-out">


        {{-- =================================================
             NAVBAR
        ================================================== --}}
        <nav
            class="h-20 bg-white border-b border-slate-100
                   flex items-center justify-end
                   px-6 lg:px-8
                   sticky top-0 z-30">

            {{-- HOLA SIEMPRE A LA DERECHA --}}
            <div class="flex items-center">

                <span class="text-sm font-medium text-slate-600">
                    Hola, {{ auth()->user()->loadMissing('perfil')->nombreParaMostrar() }}
                </span>

            </div>

        </nav>


        {{-- =================================================
             CONTENIDO
        ================================================== --}}
        <main class="flex-grow p-6 lg:p-8">

            {{ $slot }}

        </main>

    </div>

</div>


@yield('modals')


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


{{-- =========================================================
     ALERTAS
========================================================== --}}
@if (session('error') || session('success'))

    <script>

        Swal.fire({
            icon: '{{ session('success') ? 'success' : 'error' }}',
            title: '{{ session('success') ? 'Éxito' : 'Oops...' }}',
            text: '{{ session('success') ?? session('error') }}',
            confirmButtonColor: '#047857',
        });

    </script>

@endif

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
{{-- =========================================================
     SIDEBAR JAVASCRIPT
========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('sidebar');
    const mainWrapper = document.getElementById('mainWrapper');
    const overlay = document.getElementById('sidebarOverlay');

    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const toggleIcon = document.getElementById('sidebarToggleIcon');

    const MOBILE_BREAKPOINT = 1024;
    const SIDEBAR_WIDTH = 256;

    let menuOpen = false;


    // =====================================================
    // DETECTAR MÓVIL
    // =====================================================

    function isMobile() {
        return window.innerWidth < MOBILE_BREAKPOINT;
    }


    // =====================================================
    // MOSTRAR OVERLAY
    // =====================================================

    function showOverlay() {

        if (!overlay) {
            return;
        }

        overlay.classList.remove(
            'opacity-0',
            'pointer-events-none'
        );

        overlay.classList.add(
            'opacity-100',
            'pointer-events-auto'
        );
    }


    // =====================================================
    // OCULTAR OVERLAY
    // =====================================================

    function hideOverlay() {

        if (!overlay) {
            return;
        }

        overlay.classList.remove(
            'opacity-100',
            'pointer-events-auto'
        );

        overlay.classList.add(
            'opacity-0',
            'pointer-events-none'
        );
    }


    // =====================================================
    // POSICIÓN SIDEBAR
    //
    // Usamos transform directamente.
    //
    // Esto evita cualquier conflicto entre:
    // -translate-x-full
    // lg:translate-x-0
    // y JavaScript.
    // =====================================================

    function setSidebarPosition(open) {

        if (open) {

            sidebar.style.setProperty(
                'transform',
                'translate3d(0, 0, 0)',
                'important'
            );

            sidebar.style.setProperty(
                'visibility',
                'visible',
                'important'
            );

            sidebar.style.setProperty(
                'opacity',
                '1',
                'important'
            );

            sidebar.style.setProperty(
                'z-index',
                '50',
                'important'
            );

        } else {

            sidebar.style.setProperty(
                'transform',
                'translate3d(-100%, 0, 0)',
                'important'
            );

            sidebar.style.setProperty(
                'visibility',
                'visible',
                'important'
            );

            sidebar.style.setProperty(
                'opacity',
                '1',
                'important'
            );

            sidebar.style.setProperty(
                'z-index',
                '50',
                'important'
            );

        }
    }


    // =====================================================
    // POSICIÓN DE LA FLECHA
    // =====================================================

    function updateButtonPosition() {

        if (isMobile()) {

            /*
             * MÓVIL
             *
             * Cerrado:
             * flecha pegada al borde izquierdo.
             *
             * Abierto:
             * flecha queda justo al borde derecho
             * del sidebar.
             */

            if (menuOpen) {

                toggleBtn.style.setProperty(
                    'left',
                    SIDEBAR_WIDTH + 'px',
                    'important'
                );

            } else {

                toggleBtn.style.setProperty(
                    'left',
                    '0px',
                    'important'
                );

            }

        } else {

            /*
             * ESCRITORIO
             *
             * Abierto:
             * flecha exactamente en el borde derecho
             * del sidebar.
             *
             * Cerrado:
             * flecha queda en el borde izquierdo.
             */

            if (menuOpen) {

                toggleBtn.style.setProperty(
                    'left',
                    SIDEBAR_WIDTH + 'px',
                    'important'
                );

            } else {

                toggleBtn.style.setProperty(
                    'left',
                    '0px',
                    'important'
                );

            }

        }
    }


    // =====================================================
    // ACTUALIZAR ICONO
    // =====================================================

    function updateIcon() {

        if (menuOpen) {

            toggleIcon.classList.remove(
                'fa-chevron-right'
            );

            toggleIcon.classList.add(
                'fa-chevron-left'
            );

            toggleBtn.setAttribute(
                'aria-label',
                'Ocultar menú'
            );

            toggleBtn.setAttribute(
                'aria-expanded',
                'true'
            );

        } else {

            toggleIcon.classList.remove(
                'fa-chevron-left'
            );

            toggleIcon.classList.add(
                'fa-chevron-right'
            );

            toggleBtn.setAttribute(
                'aria-label',
                'Mostrar menú'
            );

            toggleBtn.setAttribute(
                'aria-expanded',
                'false'
            );

        }
    }


    // =====================================================
    // ACTUALIZAR TODO
    // =====================================================

    function updateLayout() {

        setSidebarPosition(menuOpen);

        updateButtonPosition();

        updateIcon();


        if (isMobile()) {

            /*
             * =========================================
             * MÓVIL
             * =========================================
             */

            mainWrapper.style.paddingLeft = '0';


            if (menuOpen) {

                showOverlay();

                document.body.style.overflow = 'hidden';

            } else {

                hideOverlay();

                document.body.style.overflow = '';

            }

        } else {

            /*
             * =========================================
             * ESCRITORIO
             * =========================================
             */

            hideOverlay();

            document.body.style.overflow = '';


            if (menuOpen) {

                mainWrapper.style.paddingLeft = '16rem';

            } else {

                mainWrapper.style.paddingLeft = '0';

            }

        }
    }


    // =====================================================
    // ABRIR MENÚ
    // =====================================================

    function openMenu() {

        menuOpen = true;

        updateLayout();
    }


    // =====================================================
    // CERRAR MENÚ
    // =====================================================

    function closeMenu() {

        menuOpen = false;

        updateLayout();
    }


    // =====================================================
    // TOGGLE
    // =====================================================

    function toggleMenu() {

        if (menuOpen) {

            closeMenu();

        } else {

            openMenu();

        }
    }


    // =====================================================
    // BOTÓN
    // =====================================================

    toggleBtn.addEventListener('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

        toggleMenu();

    });


    // =====================================================
    // OVERLAY
    // =====================================================

    overlay.addEventListener('click', function () {

        if (isMobile() && menuOpen) {

            closeMenu();

        }

    });


    // =====================================================
    // CERRAR AL HACER CLICK EN UN ENLACE - MÓVIL
    // =====================================================

    sidebar.querySelectorAll('a').forEach(function (link) {

        link.addEventListener('click', function () {

            if (isMobile()) {

                closeMenu();

            }

        });

    });


    // =====================================================
    // REDIMENSIONAR
    // =====================================================

    let previousMobileState = isMobile();


    window.addEventListener('resize', function () {

        const currentMobileState = isMobile();


        if (currentMobileState !== previousMobileState) {

            if (currentMobileState) {

                /*
                 * =========================================
                 * DESKTOP -> MÓVIL
                 * =========================================
                 *
                 * En móvil comienza cerrado.
                 */

                menuOpen = false;

            } else {

                /*
                 * =========================================
                 * MÓVIL -> DESKTOP
                 * =========================================
                 *
                 * En escritorio comienza abierto.
                 */

                menuOpen = true;

            }

            previousMobileState = currentMobileState;

        }


        updateLayout();

    });


    // =====================================================
    // ESTADO INICIAL
    // =====================================================

    if (isMobile()) {

        /*
         * MÓVIL:
         * sidebar cerrado.
         */

        menuOpen = false;

    } else {

        /*
         * ESCRITORIO:
         * sidebar abierto.
         */

        menuOpen = true;

    }


    updateLayout();


    // =====================================================
    // CONFIRMACIÓN DE ELIMINACIÓN
    // =====================================================

    const deleteForms =
        document.querySelectorAll('.delete-form');


    deleteForms.forEach(function (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();


            Swal.fire({

                title: '¿Está seguro de continuar?',

                text: 'Esta acción eliminará el registro de manera permanente.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#ef4444',

                cancelButtonColor: '#64748b',

                confirmButtonText: 'Sí, eliminar',

                cancelButtonText: 'Cancelar'

            }).then(function (result) {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });

    });

});
</script>


@livewireScripts
@stack('scripts')

</body>

</html>