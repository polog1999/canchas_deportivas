<x-portal-reserva-shell title="Reservar más">
    <div class="mb-6 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Nueva reserva</p>
            <h2 class="text-2xl font-bold text-gray-800">Reservar más</h2>
            <p class="text-sm text-gray-600 mt-1">
                Elige una sede y completa la reserva con tu cuenta activa.
            </p>
        </div>
        @if ($deportes->isNotEmpty())
            <div class="relative shrink-0">
                <select id="filtroDeportePortal"
                    class="appearance-none pl-4 pr-9 py-2 rounded-full bg-sky-100 text-sky-800 text-xs font-semibold border border-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-300 cursor-pointer">
                    <option value="">Deportes</option>
                    @foreach ($deportes as $deporte)
                        <option value="{{ $deporte->id }}">{{ $deporte->nombre }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-sky-700 text-[10px] pointer-events-none"></i>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5" id="gridSedesPortal">
        @forelse ($sedes as $sede)
            @php
                $deporteIds = $sede->canchas
                    ->flatMap(fn ($c) => $c->deportes->pluck('id'))
                    ->unique()
                    ->values()
                    ->implode(',');
            @endphp
            <article class="sede-card-portal bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition"
                data-deportes="{{ $deporteIds }}">
                <div class="aspect-[16/10] bg-slate-200 overflow-hidden">
                    @if (method_exists($sede, 'urlImagen') && $sede->urlImagen())
                        <img src="{{ $sede->urlImagen() }}" alt="{{ $sede->nombre }}"
                            class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-800 to-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-futbol text-white/30 text-5xl"></i>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900">{{ $sede->nombre }}</h3>
                    @if (filled($sede->direccion))
                        <p class="text-sm text-gray-500 mt-1 flex items-start gap-1.5">
                            <i class="fa-solid fa-location-dot text-gray-400 mt-0.5"></i>
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
                        <p class="text-xs text-gray-400 mt-1">
                            Horario: {{ substr((string) $sede->hora_inicio, 0, 5) }} – {{ substr((string) $sede->hora_fin, 0, 5) }}
                        </p>
                    @endif
                    <a href="{{ route('portal.reservar.deporte', ['sede' => $sede->id, 'fecha' => now()->format('Y-m-d')]) }}"
                        class="mt-4 block w-full text-center py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition">
                        Elegir deporte y turno
                    </a>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3 bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-500">
                No hay sedes activas disponibles en este momento.
            </div>
        @endforelse
    </div>

    <p id="sinSedesPortal" class="hidden bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-500">
        No hay sedes para el deporte seleccionado.
    </p>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const filtro = document.getElementById('filtroDeportePortal');
                const vacio = document.getElementById('sinSedesPortal');

                if (!filtro) return;

                filtro.addEventListener('change', () => {
                    const deporteId = filtro.value;
                    let visibles = 0;

                    document.querySelectorAll('.sede-card-portal').forEach((card) => {
                        const deportes = (card.dataset.deportes || '').split(',').filter(Boolean);
                        const ok = !deporteId || deportes.includes(deporteId);

                        card.classList.toggle('hidden', !ok);
                        if (ok) visibles++;
                    });

                    vacio?.classList.toggle('hidden', visibles > 0);
                });
            });
        </script>
    @endpush
</x-portal-reserva-shell>
