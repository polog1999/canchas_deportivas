<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige el deporte | {{ $sede->nombre }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <x-public-navbar back-href="/" back-label="Volver a sedes" />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Paso 1 de 3</p>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Elige el deporte</h1>
            <p class="text-sm text-slate-500 mt-1">
                En <span class="font-semibold text-slate-700">{{ $sede->nombre }}</span>
                · {{ $sede->direccion }}
            </p>
        </div>

        <p class="text-sm font-bold text-slate-800 mb-5">
            {{ $deportes->count() }} {{ $deportes->count() === 1 ? 'deporte disponible' : 'deportes disponibles' }}
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse ($deportes as $deporte)
                <a href="{{ route('reservar.turno', [
                        'sede' => $sede->id,
                        'deporte_id' => $deporte['id'],
                        'fecha' => $fecha,
                    ]) }}"
                    class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition group block">
                    <div class="relative aspect-[16/10] bg-slate-200 overflow-hidden">
                        <img src="{{ $deporte['imagen_url'] }}" alt="{{ $deporte['nombre'] }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1459865266369-566976b10f9e?auto=format&fit=crop&w=800&q=80'">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <p class="text-[10px] uppercase tracking-wider text-emerald-200 font-semibold">{{ $sede->nombre }}</p>
                            <h2 class="font-bold text-lg leading-tight">{{ $deporte['nombre'] }}</h2>
                        </div>
                        @if ($deporte['precioDesde'] > 0)
                            <div class="absolute top-2 right-2 bg-black/75 text-white text-xs font-bold px-2.5 py-1 rounded-lg">
                                desde S/ {{ number_format($deporte['precioDesde'], 0) }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4 flex items-center justify-between gap-2">
                        <p class="text-xs text-slate-500">
                            {{ $deporte['canchas'] }} {{ $deporte['canchas'] === 1 ? 'cancha' : 'canchas' }}
                        </p>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-[#1b5e3b]">
                            Ver turnos
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </span>
                    </div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4 bg-white rounded-2xl border border-slate-200 p-10 text-center">
                    <p class="font-semibold text-slate-700">Esta sede no tiene deportes asociados.</p>
                    <p class="text-sm text-slate-500 mt-1">Vincula canchas con deportes en administración (`canchas_deportes`).</p>
                    <a href="/" class="inline-flex mt-5 px-5 py-2.5 rounded-xl bg-[#1b5e3b] text-white text-sm font-bold">
                        Volver a sedes
                    </a>
                </div>
            @endforelse
        </div>
    </main>
</body>
</html>
