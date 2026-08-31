<x-portal-reserva-shell title="Reservar más">
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-widest text-emerald-700 mb-1">Nueva reserva</p>
        <h2 class="text-2xl font-bold text-gray-800">Reservar más</h2>
        <p class="text-sm text-gray-600 mt-1">
            Elige una sede y completa la reserva con tu cuenta activa.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse ($sedes as $sede)
            <article class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
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
</x-portal-reserva-shell>
