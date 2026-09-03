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