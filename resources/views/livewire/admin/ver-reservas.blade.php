<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Ver Reservas</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Ver reservas</h2>
                <p class="text-sm text-gray-600">
                    Calendario compacto de reservas.
                </p>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-gray-600">
                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Confirmada</span>
                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Pendiente</span>
                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Cancelada</span>
            </div>
        </div>

        <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 mb-4 flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Voucher, reserva, DNI, sede..."
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                </div>
                <select wire:model.live="filtroEstado"
                    class="w-full sm:w-40 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-1.5">
                <button type="button" wire:click="mesAnterior"
                    class="w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </button>
                <div class="min-w-[8.5rem] text-center px-1">
                    <p class="text-sm font-bold text-gray-800 capitalize leading-tight">{{ $tituloMes }}</p>
                    <p class="text-[10px] text-gray-400">{{ $totalMes }} reserva(s)</p>
                </div>
                <button type="button" wire:click="mesSiguiente"
                    class="w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
                <button type="button" wire:click="irHoy"
                    class="ml-1 px-2.5 py-1.5 rounded-lg bg-[#1b5e3b] hover:bg-[#164d31] text-white text-[11px] font-semibold">
                    Hoy
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Mini calendario --}}
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-100">
                        @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $diaSemana)
                            <div class="py-1.5 text-center text-[10px] font-bold text-gray-400">{{ $diaSemana }}</div>
                        @endforeach
                    </div>

                    <div class="p-1.5">
                        @foreach ($semanas as $semana)
                            <div class="grid grid-cols-7 gap-0.5 mb-0.5">
                                @foreach ($semana as $celda)
                                    @php
                                        $n = count($celda['reservas']);
                                        $tieneConfirmada = collect($celda['reservas'])->contains(fn ($r) => $r['estado'] === 'Confirmada');
                                        $tienePendiente = collect($celda['reservas'])->contains(fn ($r) => $r['estado'] === 'Pendiente');
                                        $tieneCancelada = collect($celda['reservas'])->contains(fn ($r) => $r['estado'] === 'Cancelada');
                                    @endphp
                                    <button type="button"
                                        wire:click="seleccionarDia('{{ $celda['fecha'] }}')"
                                        @class([
                                            'relative aspect-square rounded-lg flex flex-col items-center justify-center transition text-xs',
                                            'hover:bg-emerald-50' => $celda['del_mes'],
                                            'text-gray-300' => ! $celda['del_mes'],
                                            'text-gray-700 font-medium' => $celda['del_mes'] && ! $celda['es_hoy'],
                                            'bg-[#1b5e3b] text-white font-bold hover:bg-[#164d31]' => $celda['es_hoy'],
                                            'ring-2 ring-[#1b5e3b] ring-offset-1' => $fechaSeleccionada === $celda['fecha'] && ! $celda['es_hoy'],
                                            'bg-emerald-50' => $fechaSeleccionada === $celda['fecha'] && $celda['del_mes'] && ! $celda['es_hoy'],
                                        ])>
                                        <span>{{ $celda['dia'] }}</span>
                                        @if ($n > 0 && $celda['del_mes'])
                                            <span class="flex items-center gap-0.5 mt-0.5 h-1.5">
                                                @if ($tieneConfirmada)
                                                    <span class="w-1 h-1 rounded-full {{ $celda['es_hoy'] ? 'bg-lime-300' : 'bg-emerald-500' }}"></span>
                                                @endif
                                                @if ($tienePendiente)
                                                    <span class="w-1 h-1 rounded-full {{ $celda['es_hoy'] ? 'bg-amber-200' : 'bg-amber-400' }}"></span>
                                                @endif
                                                @if ($tieneCancelada)
                                                    <span class="w-1 h-1 rounded-full {{ $celda['es_hoy'] ? 'bg-red-200' : 'bg-red-500' }}"></span>
                                                @endif
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Lista compacta del día / mes --}}
            <div class="lg:col-span-7 xl:col-span-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-2 bg-gray-50/80">
                        <div>
                            <p class="text-sm font-bold text-gray-800">
                                @if ($fechaSeleccionada)
                                    {{ \Carbon\Carbon::parse($fechaSeleccionada)->locale('es')->translatedFormat('l d/m/Y') }}
                                @else
                                    Reservas del mes
                                @endif
                            </p>
                            <p class="text-[11px] text-gray-400">
                                {{ ($fechaSeleccionada ? $detalleDia : $reservasMes)->count() }} registro(s)
                            </p>
                        </div>
                        @if ($fechaSeleccionada)
                            <button type="button" wire:click="$set('fechaSeleccionada', null)"
                                class="text-[11px] font-semibold text-[#1b5e3b] hover:underline">
                                Ver todo el mes
                            </button>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-100 max-h-[28rem] overflow-y-auto">
                        @php
                            $lista = $fechaSeleccionada ? $detalleDia : $reservasMes;
                        @endphp

                        @forelse ($lista as $reserva)
                            @php
                                $dot = match ($reserva['estado']) {
                                    'Confirmada' => 'bg-emerald-500',
                                    'Pendiente' => 'bg-amber-400',
                                    'Cancelada' => 'bg-red-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <button type="button"
                                wire:click="seleccionarDia('{{ $reserva['fecha'] }}')"
                                class="w-full text-left px-4 py-3 hover:bg-emerald-50/50 transition flex items-start gap-3">
                                <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dot }}"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $reserva['horario'] }} · {{ $reserva['cancha'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                                {{ $reserva['sede'] }} · {{ $reserva['deporte'] }}
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-bold text-[#1b5e3b]">S/ {{ number_format($reserva['precio'], 2) }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $reserva['estado'] }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-gray-400">
                                        <span>{{ $reserva['titular'] }}</span>
                                        <span>DNI {{ $reserva['dni'] }}</span>
                                        @if ($reserva['codigo_voucher'])
                                            <span class="font-semibold text-gray-600">{{ $reserva['codigo_voucher'] }}</span>
                                        @endif
                                        <span>{{ $reserva['codigo'] }}</span>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="px-4 py-10 text-center text-sm text-gray-400">
                                <i class="fa-regular fa-calendar-xmark text-2xl mb-2 block"></i>
                                No hay reservas para mostrar.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
