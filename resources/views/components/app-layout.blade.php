<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>
        Portal
        {{ auth()->user()->getRoleNames()->first() === 'SUPERADMIN' ? 'Super Admin' : (auth()->user()->getRoleNames()->first() === 'ADMIN' ? 'Administrador' : 'Cliente') }}
        | {{ $title ?? 'Alquiler de Canchas' }}
    </title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @livewireStyles
</head>

<body class="bg-slate-100 text-slate-800 font-sans antialiased overflow-x-hidden">

    <div class="relative min-h-screen flex">

        <div id="sidebarOverlay"
            class="fixed inset-0 bg-slate-900/50 z-40 transition-opacity duration-300 opacity-0 pointer-events-none lg:hidden">
        </div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800">

            <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">
                <img src="{{ asset('favicon.png') }}" class="w-10 h-auto" alt="Logo La Molina">
                <div>
                    <h3 class="text-xs font-bold tracking-wider text-emerald-500 uppercase">
                        Canchas Deportivas
                    </h3>
                    <p class="text-[10px] text-slate-500 font-semibold tracking-tight uppercase">La Molina</p>
                </div>
            </div>

            <div class="flex-grow overflow-y-auto px-4 py-6 space-y-6">
                <ul class="space-y-1">
                    {{--
                    @activeRole('SUPERADMIN')
                        <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Super Administrador
                        </div>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.dashboard') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-chart-line w-5 text-center text-emerald-500"></i> Control Panel
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.users.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-users-gear w-5 text-center text-emerald-500"></i> Usuarios del Sistema
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors hover:bg-slate-800 hover:text-white">
                                <i class="fa-solid fa-gears w-5 text-center text-emerald-500"></i> Configuración Global
                            </a>
                        </li>
                    @endactiveRole

                    @activeRole('ADMIN')
                        <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Gestión Municipal
                        </div>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.dashboard') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-house w-5 text-center text-emerald-500"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.talleres.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.talleres.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-futbol w-5 text-center text-emerald-500"></i> Canchas / Espacios
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.matriculas.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.matriculas.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-calendar-check w-5 text-center text-emerald-500"></i> Reservas Activas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.lugares.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.lugares.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-location-dot w-5 text-center text-emerald-500"></i> Sedes Deportivas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pagos.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('admin.pagos.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-money-bill-wave w-5 text-center text-emerald-500"></i> Reporte de Ingresos
                            </a>
                        </li>
                    @endactiveRole

                    @activeRole('CLIENTE')
                        <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Servicios
                        </div>
                        <li>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors hover:bg-slate-800 hover:text-white">
                                <i class="fa-solid fa-magnifying-glass w-5 text-center text-emerald-500"></i> Buscar Canchas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('portal.pagos.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('portal.pagos.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-clock-history w-5 text-center text-emerald-500"></i> Mis Reservas
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors hover:bg-slate-800 hover:text-white">
                                <i class="fa-solid fa-wallet w-5 text-center text-emerald-500"></i> Pagos Realizados
                            </a>
                        </li>
                    @endactiveRole
--}}
                    @can('tusnes::ver')
                        <div class="px-3 mt-6 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Gestionar Tusne
                        </div>
                        <li>
                            <a href="{{ route('tusne.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('tusne.index') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-id-card w-5 text-center text-emerald-500"></i> Tusne
                            </a>
                        </li>
                    @endcan
                    @can('usuarios::ver')
                        <div class="px-3 mt-6 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Ajustes de Usuario
                        </div>
                        <li>
                            <a href="{{ route('users') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ Route::is('users') ? 'bg-emerald-700 text-white shadow-md shadow-emerald-900/20' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-id-card w-5 text-center text-emerald-500"></i> Usuarios
                            </a>
                        </li>
                    @endcan

                </ul>
            </div>

            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 text-sm font-semibold rounded-lg bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white transition-colors duration-150">
                        <i class="fa-solid fa-power-off"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div id="mainWrapper"
            class="flex-grow flex flex-col min-w-0 w-full lg:pl-64 transition-all duration-300 ease-in-out">

            <nav
                class="h-20 bg-white border-b border-slate-100 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-30">

                <div class="flex items-center">
                    <button id="menuToggleBtn"
                        class="flex flex-col gap-1.5 justify-center items-center w-8 h-8 rounded-lg hover:bg-slate-50 transition-colors focus:outline-none">
                        <span class="w-6 h-0.5 bg-slate-600 rounded-full transition-all duration-300"
                            id="hamburger-1"></span>
                        <span class="w-6 h-0.5 bg-slate-600 rounded-full transition-all duration-300"
                            id="hamburger-2"></span>
                        <span class="w-6 h-0.5 bg-slate-600 rounded-full transition-all duration-300"
                            id="hamburger-3"></span>
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-slate-600 hidden sm:block">
                        Hola, {{ Auth::user()->nombres ?? 'Usuario' }}
                    </span>

                    {{-- <div class="relative">
                        <a href="{{ route('seleccionar.rol') }}" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-lg text-xs font-bold text-emerald-800 transition-colors">
                            <i class="fa-solid fa-user-shield text-emerald-600"></i>
                            <span>{{ session('active_role', 'CLIENTE') }}</span>
                        </a>
                    </div> --}}
                </div>
            </nav>

            <main class="flex-grow p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>
    </div>

    @yield('modals')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')

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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('menuToggleBtn');
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('mainWrapper');
            const overlay = document.getElementById('sidebarOverlay');

            // Elementos del icono de hamburguesa
            const h1 = document.getElementById('hamburger-1');
            const h2 = document.getElementById('hamburger-2');
            const h3 = document.getElementById('hamburger-3');

            function toggleMenu() {
                const isMobile = window.innerWidth <= 1024;

                if (isMobile) {
                    // Control de Sidebar en Móvil
                    sidebar.classList.toggle('translate-x-0');
                    overlay.classList.toggle('opacity-100');
                    overlay.classList.toggle('pointer-events-auto');

                    if (sidebar.classList.contains('translate-x-0')) {
                        document.body.style.overflow = 'hidden';
                        transformHamburger(true);
                    } else {
                        document.body.style.overflow = '';
                        transformHamburger(false);
                    }
                } else {
                    // Control de Sidebar en Escritorio
                    sidebar.classList.toggle('lg:-translate-x-full');
                    mainWrapper.classList.toggle('lg:pl-0');

                    const isCollapsed = sidebar.classList.contains('lg:-translate-x-full');
                    transformHamburger(isCollapsed);
                }
            }

            function transformHamburger(active) {
                if (active) {
                    h1.style.transform = 'translateY(8px) rotate(45deg)';
                    h2.style.opacity = '0';
                    h3.style.transform = 'translateY(-8px) rotate(-45deg)';
                } else {
                    h1.style.transform = '';
                    h2.style.opacity = '';
                    h3.style.transform = '';
                }
            }

            menuBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);

            // Ajuste automático si se redimensiona la ventana
            window.addEventListener('resize', () => {
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('translate-x-0');
                    overlay.classList.remove('opacity-100', 'pointer-events-auto');
                    document.body.style.overflow = '';
                    transformHamburger(sidebar.classList.contains('lg:-translate-x-full'));
                } else {
                    transformHamburger(sidebar.classList.contains('translate-x-0'));
                }
            });

            // Confirmación de eliminación global para formularios con clase delete-form
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @livewireScripts
</body>

</html>
