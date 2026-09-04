<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Reprogramación</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-5">
            <h2 class="text-2xl font-bold text-gray-800">Reprogramación</h2>
            <p class="text-sm text-gray-600">
                Mueve una reserva ya pagada a otro turno con la misma tarifa. El horario anterior queda libre al confirmar.
            </p>
        </div>

        @if (session('reprogramacion_ok'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('reprogramacion_ok') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Buscador y listado de reservas --}}
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-3 border-b border-gray-100">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Voucher, N° reserva, DNI, correo..."
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>

                    <div class="max-h-[34rem] overflow-y-auto divide-y divide-gray-100">
                        @forelse ($reservas as $item)
                            <button type="button" wire:click="seleccionarReserva({{ $item->id }})"
                                @class([
                                    'w-full text-left px-3 py-2.5 transition hover:bg-emerald-50',
                                    'bg-emerald-50 border-l-4 border-[#1b5e3b]' => $reservaId === $item->id,
                                ])>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-gray-800">
                                        RES-{{ str_pad((string) $item->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-[#1b5e3b]">
                                        S/ {{ number_format((float) $item->precio_total, 2) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-600 truncate">
                                    {{ $item->usuario?->nombreCompleto() ?? '—' }}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    {{ $item->canchaVigente()?->nombre ?? '—' }} ·
                                    {{ $item->horaInicioVigente()->format('d/m/Y H:i') }}
                                    @if ($item->fueReprogramada())
                                        <span class="ml-1 inline-block rounded bg-amber-100 px-1 text-[10px] font-semibold text-amber-700">
                                            reprogramada
                                        </span>
                                    @endif
                                </p>
                            </button>
                        @empty
                            <p class="px-3 py-6 text-center text-xs text-gray-400">
                                No hay reservas confirmadas que coincidan.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Detalle y grilla --}}
            <div class="lg:col-span-8 xl:col-span-9">
                @if (! $reserva)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center">
                        <i class="fa-regular fa-calendar text-3xl text-gray-300"></i>
                        <p class="mt-3 text-sm text-gray-500">Elige una reserva de la lista para reprogramarla.</p>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Turno vigente</p>
                                <h3 class="text-lg font-bold text-gray-800">
                                    RES-{{ str_pad((string) $reserva->id, 4, '0', STR_PAD_LEFT) }} ·
                                    {{ $reserva->usuario?->nombreCompleto() ?? '—' }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $reserva->cancha?->sede?->nombre }} — {{ $reserva->canchaVigente()?->nombre }} ·
                                    {{ $reserva->horaInicioVigente()->locale('es')->translatedFormat('D d/m/Y') }},
                                    {{ $reserva->horaInicioVigente()->format('H:i') }} a {{ $reserva->horaFinVigente()->format('H:i') }}
                                </p>
                                @if ($reserva->fueReprogramada())
                                    <p class="text-[11px] text-gray-400">
                                        Reserva original (sin modificar):
                                        {{ $reserva->cancha?->nombre }},
                                        {{ $reserva->hora_inicio?->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            <button type="button" wire:click="limpiarSeleccion"
                                class="self-start text-xs text-gray-500 hover:text-gray-700">
                                <i class="fa-solid fa-xmark"></i> Cerrar
                            </button>
                        </div>

                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <p class="text-gray-400">Monto pagado</p>
                                <p class="font-bold text-gray-800">S/ {{ number_format($contexto['monto_original'], 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <p class="text-gray-400">Duración</p>
                                <p class="font-bold text-gray-800">{{ $contexto['duracion'] }} min</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <p class="text-gray-400">Tarifa</p>
                                <p class="font-bold text-gray-800 truncate">
                                    {{ $contexto['tusne']?->descripcion_local ?? 'Precio de cancha' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2">
                                <p class="text-gray-400">Turno permitido</p>
                                <p class="font-bold text-gray-800">
                                    @switch($contexto['tusne']?->horario_turno)
                                        @case('noche') Solo nocturno @break
                                        @case('dia') Solo diurno @break
                                        @case(null) Sin restricción @break
                                        @default Todo el día
                                    @endswitch
                                </p>
                            </div>
                        </div>

                        @if ($contexto['tarifa_cambio'])
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 flex items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                <span>
                                    La tarifa cambió desde que se pagó: se cobró
                                    <strong>S/ {{ number_format($contexto['monto_original'], 2) }}</strong> y hoy ese mismo
                                    turno cuesta <strong>S/ {{ number_format($contexto['monto_actual'], 2) }}</strong>.
                                    No se puede reprogramar sin ajustar el monto.
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Navegación de fecha --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="diaAnterior"
                                class="w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            <p class="min-w-[9rem] text-center text-sm font-bold text-gray-800 capitalize">{{ $fechaLabel }}</p>
                            <button type="button" wire:click="diaSiguiente"
                                class="w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                            <input type="date" wire:model.live="fecha"
                                class="ml-2 px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-gray-600">
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span> Disponible</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200"></span> No disponible</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-400"></span> Inicio del turno actual</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded border-2 border-dashed border-amber-400"></span> Horas que ya ocupa</span>
                        </div>
                    </div>

                    <div class="-mt-2 mb-4 flex items-start gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] text-sky-800">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>
                            Cada casilla es la <strong>hora de inicio</strong> de un turno de
                            <strong>{{ $contexto['duracion'] }} minutos</strong>, no una hora suelta.
                            Por ejemplo, la casilla de las 11h significa mover la reserva a
                            11:00 – {{ sprintf('%02d:00', 11 + $contexto['duracion'] / 60) }}.
                            El borde ámbar marca las horas que la reserva ocupa ahora.
                        </span>
                    </div>

                    {{-- Grilla --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
                        @if (empty($grilla['canchas']))
                            <p class="px-4 py-8 text-center text-xs text-gray-400">
                                No hay canchas en esta sede con la misma tarifa de la reserva.
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100">
                                            <th class="sticky left-0 bg-gray-50 px-3 py-2 text-left font-bold text-gray-500 min-w-[9rem]">Cancha</th>
                                            @foreach ($grilla['horas'] as $h)
                                                <th class="px-1 py-2 text-center font-bold text-gray-500 min-w-[3rem]">
                                                    {{ sprintf('%02d', $h) }}h
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($grilla['canchas'] as $cancha)
                                            <tr>
                                                <td class="sticky left-0 bg-white px-3 py-2 font-semibold text-gray-700">
                                                    {{ $cancha['nombre'] }}
                                                </td>
                                                @foreach ($grilla['horas'] as $h)
                                                    @php $slot = $cancha['slots'][$h]; @endphp
                                                    <td class="px-0.5 py-1">
                                                        <button type="button"
                                                            @if ($slot['estado'] === 'disponible' && ! $contexto['tarifa_cambio'])
                                                                wire:click="elegirSlot({{ $cancha['id'] }}, {{ $h }})"
                                                            @else
                                                                disabled
                                                            @endif
                                                            title="{{ $slot['motivo'] }}{{ $slot['estado'] === 'disponible' ? ' · S/ '.number_format((float) $slot['precio'], 2) : '' }}"
                                                            @class([
                                                                'w-full h-9 rounded transition',
                                                                'bg-emerald-500 hover:bg-emerald-600 cursor-pointer' => $slot['estado'] === 'disponible' && ! $contexto['tarifa_cambio'],
                                                                'bg-emerald-200 cursor-not-allowed' => $slot['estado'] === 'disponible' && $contexto['tarifa_cambio'],
                                                                'bg-amber-400 cursor-not-allowed' => $slot['estado'] === 'actual',
                                                                'bg-gray-200 cursor-not-allowed' => $slot['estado'] === 'bloqueado',
                                                                // Horas que la reserva ocupa hoy, sin ser su hora de inicio.
                                                                'border-2 border-amber-400 border-dashed' => $slot['dentro_actual'] && $slot['estado'] !== 'actual',
                                                                'ring-2 ring-offset-1 ring-[#1b5e3b]' => $slotCanchaId === $cancha['id'] && $slotHora === $h,
                                                            ])>
                                                        </button>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Historial --}}
                    @if ($historial->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">
                                Historial de reprogramaciones
                            </p>
                            <ul class="space-y-2">
                                @foreach ($historial as $registro)
                                    <li class="text-xs text-gray-600 border-l-2 border-gray-200 pl-3">
                                        <span class="font-semibold text-gray-800">
                                            {{ $registro->canchaAnterior?->nombre }}
                                            {{ $registro->hora_inicio_anterior->format('d/m/Y H:i') }}
                                        </span>
                                        pasó a
                                        <span class="font-semibold text-gray-800">
                                            {{ $registro->canchaNueva?->nombre }}
                                            {{ $registro->hora_inicio_nueva->format('d/m/Y H:i') }}
                                        </span>
                                        <span class="text-gray-400">
                                            · {{ $registro->autorizadoPor?->nombreCompleto() ?? 'Sistema' }}
                                            · {{ $registro->creado_en?->format('d/m/Y H:i') }}
                                        </span>
                                        @if ($registro->motivo)
                                            <p class="text-gray-500 italic">"{{ $registro->motivo }}"</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Confirmación --}}
    @if ($reserva && $slotCanchaId !== null && $slotHora !== null)
        @php
            $canchaDestino = collect($grilla['canchas'])->firstWhere('id', $slotCanchaId);
            $inicioDestino = \Carbon\Carbon::parse($fecha)->setTime($slotHora, 0);
            $finDestino = $inicioDestino->copy()->addMinutes($contexto['duracion']);
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="border-b border-gray-100 px-5 py-3">
                    <h3 class="text-base font-bold text-gray-800">Confirmar reprogramación</h3>
                </div>

                <div class="px-5 py-4 space-y-3 text-sm">
                    <div class="rounded-lg bg-gray-50 px-3 py-2 text-xs">
                        <p class="text-gray-500">
                            De
                            <strong class="text-gray-800">
                                {{ $reserva->canchaVigente()?->nombre }},
                                {{ $reserva->horaInicioVigente()->format('d/m/Y H:i') }}
                            </strong>
                        </p>
                        <p class="text-gray-500">
                            A
                            <strong class="text-[#1b5e3b]">
                                {{ $canchaDestino['nombre'] ?? '—' }},
                                {{ $inicioDestino->format('d/m/Y H:i') }} a {{ $finDestino->format('H:i') }}
                            </strong>
                        </p>
                        <p class="mt-1 text-gray-500">
                            Monto: <strong class="text-gray-800">S/ {{ number_format($contexto['monto_original'], 2) }}</strong>
                            (sin cambios)
                        </p>
                    </div>

                    <p class="text-xs text-gray-500">
                        El turno {{ $reserva->horaInicioVigente()->format('H:i') }} del
                        {{ $reserva->horaInicioVigente()->format('d/m/Y') }} quedará libre para otra persona.
                        La reserva original no se modifica: queda como registro de lo que se pagó.
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Motivo (opcional)</label>
                        <textarea wire:model="motivo" rows="2"
                            placeholder="Ej. el cliente no pudo asistir"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    @error('hora')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('cancha_id')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-3">
                    <button type="button" wire:click="cerrarConfirmacion"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" wire:click="confirmar" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg bg-[#1b5e3b] hover:bg-[#164d31] text-xs font-semibold text-white disabled:opacity-60">
                        <span wire:loading.remove wire:target="confirmar">Reprogramar</span>
                        <span wire:loading wire:target="confirmar">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
