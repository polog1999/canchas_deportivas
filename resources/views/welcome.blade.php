<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Canchas | Municipalidad de La Molina</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-slide { transition: opacity 0.6s ease; }
        .hero-slide.hidden-slide { opacity: 0; pointer-events: none; position: absolute; inset: 0; }
        .hero-slide.active-slide { opacity: 1; position: relative; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <x-public-navbar />

    {{-- Hero --}}
    <section class="relative bg-slate-900">
        <div id="heroCarousel" class="relative min-h-[420px] sm:min-h-[480px] lg:min-h-[520px] overflow-hidden">
            @forelse ($slides as $index => $slide)
                <div class="hero-slide {{ $index === 0 ? 'active-slide' : 'hidden-slide' }}" data-slide="{{ $index }}">
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $slide->urlImagen() }}')"></div>
                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-28 lg:py-32">
                        <h1 class="max-w-3xl text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-[0_2px_8px_rgba(0,0,0,0.55)]">
                            {{ $slide->titulo }}
                        </h1>
                        <a href="{{ $slide->enlace_boton }}"
                            class="inline-flex items-center gap-2 mt-8 px-8 py-3 rounded-full bg-[#1b5e3b] hover:bg-[#164d31] text-white font-bold text-sm shadow-lg transition">
                            {{ $slide->texto_boton }}
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="hero-slide active-slide" data-slide="0">
                    <div class="absolute inset-0 bg-cover bg-center"
                        style="background-image: url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1600&q=80')"></div>
                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-28 lg:py-32">
                        <h1 class="max-w-3xl text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-[0_2px_8px_rgba(0,0,0,0.55)]">
                            Reserva tu cancha favorita en línea y juega con pasión en La Molina
                        </h1>
                        <a href="#gridSedes"
                            class="inline-flex items-center gap-2 mt-8 px-8 py-3 rounded-full bg-[#1b5e3b] hover:bg-[#164d31] text-white font-bold text-sm shadow-lg transition">
                            Reservar cancha
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforelse

            @if ($slides->count() > 1)
                <button type="button" id="heroPrev"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:bg-white shadow flex items-center justify-center z-10">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" id="heroNext"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:bg-white shadow flex items-center justify-center z-10">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @endif
        </div>

        {{-- Barra de búsqueda --}}
        <div class="relative z-20 -mt-10 sm:-mt-12 px-4 sm:px-6 lg:px-8 pb-4">
            <div class="max-w-5xl mx-auto bg-[#1e3a5f] rounded-xl shadow-2xl p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-sky-200 mb-1.5">Busca tu espacio deportivo</label>
                    <div class="relative">
                        <select id="filtroSede"
                            class="w-full appearance-none bg-white rounded-lg px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-400">
                            <option value="">Todas las sedes</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sky-200 mb-1.5">Fecha</label>
                    <div class="relative">
                        <input type="date" id="filtroFecha" value="{{ now()->format('Y-m-d') }}"
                            class="w-full bg-white rounded-lg px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-400">
                        <i class="fa-regular fa-calendar absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Espacios --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl sm:text-4xl font-bold text-[#1e3a5f]">Nuestros espacios</h2>
                <p class="text-slate-500 mt-1 text-sm">Selecciona un complejo y reserva tu fecha disponible</p>
            </div>
            <div class="relative">
                <select id="filtroDeporte"
                    class="appearance-none pl-4 pr-9 py-2 rounded-full bg-sky-100 text-sky-800 text-xs font-semibold border border-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-300 cursor-pointer">
                    <option value="">Deportes</option>
                    @foreach ($deportes as $deporte)
                        <option value="{{ $deporte->id }}">{{ $deporte->nombre }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-sky-700 text-[10px] pointer-events-none"></i>
            </div>
        </div> 

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8" id="gridSedes">
            @forelse ($sedes as $sede)
                @php
                    $deporteIds = $sede->canchas
                        ->flatMap(fn ($c) => $c->deportes->pluck('id'))
                        ->unique()
                        ->values()
                        ->implode(',');
                @endphp
                <article class="sede-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow"
                    data-sede-id="{{ $sede->id }}"
                    data-deportes="{{ $deporteIds }}">
                    <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                        <img src="{{ $sede->urlImagen() }}" alt="{{ $sede->nombre }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1459865266369-566976b10f9e?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="p-5 sm:p-6">
                        <h3 class="text-xl font-bold text-[#1e3a5f]">{{ $sede->nombre }}</h3>
                        @if (filled($sede->direccion))
                            <p class="text-sm text-slate-500 mt-1 flex items-start gap-1.5">
                                <i class="fa-solid fa-location-dot text-slate-400 mt-0.5"></i>
                                <span>{{ $sede->direccion }}</span>
                            </p>
                        @endif
                        @if (filled($sede->enlace_mapas))
                            <a href="{{ $sede->enlace_mapas }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Ver en el mapa
                            </a>
                        @endif
                        @if ($sede->hora_inicio && $sede->hora_fin)
                            <p class="text-xs text-slate-400 mt-1">
                                Horario: {{ substr((string) $sede->hora_inicio, 0, 5) }} – {{ substr((string) $sede->hora_fin, 0, 5) }}
                            </p>
                        @endif
                        <a href="{{ route('reservar.deporte', ['sede' => $sede->id, 'fecha' => now()->format('Y-m-d')]) }}"
                            class="js-reservar-sede mt-5 block w-full text-center py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white font-bold text-sm transition"
                            data-sede="{{ $sede->id }}">
                            Reservar fecha
                        </a>
                    </div>
                </article>
            @empty
                <p class="sm:col-span-2 text-center text-slate-500 py-12">
                    No hay sedes activas registradas por el momento.
                </p>
            @endforelse
        </div>
        <p id="mensajeSinSedes" class="hidden text-center text-slate-500 py-12 sm:col-span-2" style="display:none">
            No hay sedes para el deporte seleccionado.
        </p>
    </main>

    {{-- Footer --}}
    <footer class="mt-8">
        <div class="h-1.5 bg-lime-400"></div>
        <div class="bg-[#1b5e3b] text-white py-6">
            <p class="text-center text-sm px-4">
                © {{ date('Y') }} Municipalidad de La Molina - Todos los derechos reservados
            </p>
        </div>
    </footer>

    <script>
        (function () {
            const slides = Array.from(document.querySelectorAll('.hero-slide'));
            let current = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active-slide', i === index);
                    slide.classList.toggle('hidden-slide', i !== index);
                });
                current = index;
            }

            document.getElementById('heroPrev')?.addEventListener('click', () => {
                showSlide((current - 1 + slides.length) % slides.length);
            });

            document.getElementById('heroNext')?.addEventListener('click', () => {
                showSlide((current + 1) % slides.length);
            });

            setInterval(() => {
                showSlide((current + 1) % slides.length);
            }, 8000);

            const filtroSede = document.getElementById('filtroSede');
            const filtroDeporte = document.getElementById('filtroDeporte');
            const filtroFecha = document.getElementById('filtroFecha');
            const deporteBase = @json(url('/reservar/deporte'));
            const vacioSedes = document.getElementById('mensajeSinSedes');

            function actualizarEnlaces() {
                const fecha = filtroFecha?.value || @json(now()->format('Y-m-d'));

                document.querySelectorAll('.js-reservar-sede').forEach((a) => {
                    const sede = a.dataset.sede;
                    a.href = deporteBase
                        + '?sede=' + encodeURIComponent(sede)
                        + '&fecha=' + encodeURIComponent(fecha);
                });
            }

            function filtrarSedes() {
                const sedeId = filtroSede?.value || '';
                const deporteId = filtroDeporte?.value || '';
                let visibles = 0;

                document.querySelectorAll('.sede-card').forEach((card) => {
                    const matchSede = !sedeId || card.dataset.sedeId === sedeId;
                    const deps = (card.dataset.deportes || '').split(',').filter(Boolean);
                    const matchDeporte = !deporteId || deps.includes(deporteId);
                    const ok = matchSede && matchDeporte;
                    card.style.display = ok ? '' : 'none';
                    if (ok) visibles++;
                });

                if (vacioSedes) {
                    vacioSedes.style.display = visibles === 0 ? 'block' : 'none';
                }

                actualizarEnlaces();
            }

            filtroSede?.addEventListener('change', filtrarSedes);
            filtroDeporte?.addEventListener('change', filtrarSedes);
            filtroFecha?.addEventListener('change', actualizarEnlaces);
            actualizarEnlaces();
        })();
    </script>
</body>
</html>
