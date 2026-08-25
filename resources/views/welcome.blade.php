<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Molitickets | Reserva de Canchas - Municipalidad de La Molina</title>
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

    {{-- Header --}}
    <header class="bg-[#1b5e3b] text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="/" class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('logo_municipal_negro.png') }}" alt="Municipalidad de La Molina"
                    class="h-10 w-auto bg-white rounded-md px-2 py-1 object-contain"
                    onerror="this.style.display='none'">
                <div class="leading-tight hidden sm:block">
                    <p class="text-[10px] uppercase tracking-widest text-emerald-200">Municipalidad de</p>
                    <p class="font-bold text-sm">La Molina</p>
                </div>
            </a>

            <div class="flex items-center gap-4 sm:gap-6">
                <div class="hidden md:flex items-center gap-3 text-white/90">
                    <a href="#" class="hover:text-white transition" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-white transition" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold hover:text-emerald-200 transition whitespace-nowrap">
                        <i class="fa-regular fa-user"></i>
                        <span>Mi cuenta</span>
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

    {{-- Hero --}}
    <section class="relative bg-slate-900">
        <div id="heroCarousel" class="relative min-h-[420px] sm:min-h-[480px] lg:min-h-[520px] overflow-hidden">
            @php
                $slides = [
                    [
                        'image' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1600&q=80',
                        'title' => 'Reserva tu cancha favorita en línea y juega con pasión en La Molina',
                    ],
                    [
                        'image' => 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=1600&q=80',
                        'title' => 'Encuentra el espacio deportivo ideal para tu equipo y tu familia',
                    ],
                ];
            @endphp

            @foreach ($slides as $index => $slide)
                <div class="hero-slide {{ $index === 0 ? 'active-slide' : 'hidden-slide' }}" data-slide="{{ $index }}">
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $slide['image'] }}')"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/20"></div>
                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-28 lg:py-32">
                        <h1 class="max-w-3xl text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                            {{ $slide['title'] }}
                        </h1>
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                            class="inline-flex items-center gap-2 mt-8 px-8 py-3 rounded-full bg-lime-500 hover:bg-lime-400 text-emerald-950 font-bold text-sm shadow-lg transition">
                            Reservar cancha
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach

            <button type="button" id="heroPrev"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:bg-white shadow flex items-center justify-center z-10">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" id="heroNext"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:bg-white shadow flex items-center justify-center z-10">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        {{-- Barra de búsqueda --}}
        <div class="relative z-20 -mt-10 sm:-mt-12 px-4 sm:px-6 lg:px-8 pb-4">
            <div class="max-w-5xl mx-auto bg-[#1e3a5f] rounded-xl shadow-2xl p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-sky-200 mb-1.5">Busca tu espacio deportivo</label>
                    <div class="relative">
                        <select class="w-full appearance-none bg-white rounded-lg px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-400">
                            <option>Complejo deportivo</option>
                            <option>Estadio Fútbol 11</option>
                            <option>Complejo Musa</option>
                            <option>Complejo El Valle</option>
                            <option>Complejo Covima</option>
                            <option>Complejo Kohatsu</option>
                            <option>Campo de tenis</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-sky-200 mb-1.5">Fecha</label>
                    <div class="relative">
                        <input type="date" value="{{ now()->format('Y-m-d') }}"
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
            <div class="flex flex-wrap gap-2">
                <button type="button" class="px-4 py-2 rounded-full bg-sky-100 text-sky-800 text-xs font-semibold border border-sky-200">
                    Tipo de evento <i class="fa-solid fa-chevron-down ml-1"></i>
                </button>
                <button type="button" class="px-4 py-2 rounded-full bg-sky-100 text-sky-800 text-xs font-semibold border border-sky-200">
                    Tipo de evento <i class="fa-solid fa-chevron-down ml-1"></i>
                </button>
                <button type="button" class="px-4 py-2 rounded-full bg-sky-100 text-sky-800 text-xs font-semibold border border-sky-200">
                    Categoría <i class="fa-solid fa-chevron-down ml-1"></i>
                </button>
            </div>
        </div>

        @php
            $espacios = [
                ['nombre' => 'Estadio Fútbol 11', 'imagen' => 'https://images.unsplash.com/photo-1459865266369-566976b10f9e?auto=format&fit=crop&w=800&q=80'],
                ['nombre' => 'Complejo Musa', 'imagen' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=800&q=80'],
                ['nombre' => 'Complejo "El Valle"', 'imagen' => 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=800&q=80'],
                ['nombre' => 'Complejo Covima', 'imagen' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80'],
                ['nombre' => 'Complejo "Kohatsu"', 'imagen' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=800&q=80'],
                ['nombre' => 'Campo de tenis', 'imagen' => 'https://images.unsplash.com/photo-1554068865-524785ef8b6f?auto=format&fit=crop&w=800&q=80'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8">
            @foreach ($espacios as $espacio)
                <article class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                        <img src="{{ $espacio['imagen'] }}" alt="{{ $espacio['nombre'] }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-5 sm:p-6">
                        <h3 class="text-xl font-bold text-[#1e3a5f]">{{ $espacio['nombre'] }}</h3>
                        <p class="text-sm text-slate-500 mt-1">Municipalidad de La Molina</p>
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                            class="mt-5 block w-full text-center py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white font-bold text-sm transition">
                            Reservar fecha
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </main>

    {{-- Footer --}}
    <footer class="mt-8">
        <div class="h-1.5 bg-lime-400"></div>
        <div class="bg-[#1b5e3b] text-white py-6">
            <p class="text-center text-sm px-4">
                © {{ date('Y') }} Molitickets - Todos los derechos reservados - Municipalidad de La Molina
            </p>
        </div>
    </footer>

    {{-- Barra lateral decorativa (como maqueta) --}}
    <div class="fixed left-0 top-1/2 -translate-y-1/2 z-30 hidden xl:flex flex-col gap-2">
        <div class="w-10 h-10 bg-[#1e3a5f] text-white flex items-center justify-center rounded-r-lg shadow">
            <i class="fa-solid fa-briefcase text-sm"></i>
        </div>
        <div class="w-10 h-10 bg-[#1e3a5f] text-white flex items-center justify-center rounded-r-lg shadow">
            <i class="fa-solid fa-user text-sm"></i>
        </div>
    </div>

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
        })();
    </script>
</body>
</html>
