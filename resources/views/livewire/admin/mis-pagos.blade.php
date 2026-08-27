<div class="p-6 bg-gray-50 min-h-screen">
    <x-slot name="title">Mis Pagos</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Mis Pagos</h2>
                <p class="text-sm text-gray-600">
                    Constancia de pagos por reservas de canchas.
                </p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por voucher, DNI, sede, cancha..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">Pedido</th>
                            <th class="py-4 px-6">Fecha pago</th>
                            <th class="py-4 px-6">Titular</th>
                            <th class="py-4 px-6">Reserva</th>
                            <th class="py-4 px-6">Monto</th>
                            <th class="py-4 px-6">Estado</th>
                            <th class="py-4 px-6 text-center">Voucher</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">
                        @forelse ($pagos as $pago)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-900">#{{ $pago['nro_pedido'] }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $pago['codigo_voucher'] ?? $pago['nro_operacion'] }}</div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">{{ $pago['fecha_pago'] }}</td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-gray-900">{{ $pago['titular'] }}</div>
                                    <div class="text-[11px] text-gray-400">DNI {{ $pago['dni'] }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-gray-900">{{ $pago['sede'] }} · {{ $pago['cancha'] }}</div>
                                    <div class="text-[11px] text-gray-500">
                                        {{ $pago['deporte'] }} · {{ $pago['fecha_turno'] }} · {{ $pago['horario'] }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-semibold whitespace-nowrap">
                                    S/ {{ number_format($pago['monto'], 2) }}
                                </td>
                                <td class="py-4 px-6">
                                    @if ($pago['estado'] === 'Pagado')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Pagado
                                        </span>
                                    @elseif ($pago['estado'] === 'Gratuito')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-sky-100 text-sky-800">
                                            Gratuito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $pago['estado'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button type="button" wire:click="verVoucher({{ $pago['id'] }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition">
                                        <i class="fa-solid fa-receipt"></i>
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 px-6 text-center text-gray-500">
                                    No hay pagos para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Voucher / ticket ficticio --}}
    @if ($mostrarVoucher && $pagoSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            wire:keydown.escape.window="cerrarVoucher">
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarVoucher"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-800">Constancia de pago</h3>
                    <button type="button" wire:click="cerrarVoucher"
                        class="w-8 h-8 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="px-6 py-5 text-center text-sm text-gray-800 font-mono">
                    <img src="{{ asset('logo_municipal_negro.png') }}" alt="La Molina"
                        class="h-12 mx-auto mb-2 object-contain" onerror="this.style.display='none'">
                    <p class="font-bold text-xs tracking-wide">MUNICIPALIDAD DE LA MOLINA</p>
                    <p class="text-[11px] text-gray-500">RUC 20131372175</p>
                    <p class="text-[11px] text-gray-500">Av. Elías Aparicio 740 - La Molina</p>
                    <p class="mt-3 font-bold text-xs uppercase tracking-wider text-[#1b5e3b]">Reserva de canchas deportivas</p>
                    <p class="text-[11px] text-gray-400">molicanchas.munimolina.gob.pe</p>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>

                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">N° PEDIDO:</span> <strong>{{ $pagoSeleccionado['nro_pedido'] }}</strong></p>
                        <p><span class="text-gray-500">CÓDIGO VOUCHER:</span> <strong>{{ $pagoSeleccionado['codigo_voucher'] ?? '—' }}</strong></p>
                        <p><span class="text-gray-500">N° OPERACIÓN:</span> <strong>{{ $pagoSeleccionado['nro_operacion'] }}</strong></p>
                        <p><span class="text-gray-500">FECHA Y HORA:</span> {{ $pagoSeleccionado['fecha_pago'] }}</p>
                    </div>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 text-left mb-2">Pagado por</p>
                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">NOMBRE:</span> <strong>{{ $pagoSeleccionado['titular'] }}</strong></p>
                        <p><span class="text-gray-500">DNI:</span> {{ $pagoSeleccionado['dni'] }}</p>
                    </div>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 text-left mb-2">Detalle</p>
                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">CONCEPTO:</span> {{ $pagoSeleccionado['concepto'] }}</p>
                        <p><span class="text-gray-500">SEDE:</span> {{ $pagoSeleccionado['sede'] }}</p>
                        <p><span class="text-gray-500">CANCHA:</span> {{ $pagoSeleccionado['cancha'] }}</p>
                        <p><span class="text-gray-500">DEPORTE:</span> {{ $pagoSeleccionado['deporte'] }}</p>
                        <p><span class="text-gray-500">TURNO:</span> {{ $pagoSeleccionado['fecha_turno'] }} · {{ $pagoSeleccionado['horario'] }}</p>
                        <p><span class="text-gray-500">MEDIO:</span> {{ $pagoSeleccionado['medio_pago'] }}</p>
                    </div>

                    <div class="mt-5 border-2 border-gray-800 grid grid-cols-2 text-left">
                        <div class="px-3 py-2 font-bold text-xs border-r border-gray-800">TOTAL PAGADO</div>
                        <div class="px-3 py-2 font-bold text-sm text-right">
                            S/ {{ number_format($pagoSeleccionado['monto'], 2) }}
                        </div>
                    </div>

                    <p class="mt-5 text-xs font-semibold">¡Gracias por tu reserva!</p>
                    <p class="text-[11px] text-gray-400">Conserve este ticket como constancia.</p>
                    <p class="text-[11px] text-gray-400 mt-1">Canchas Deportivas — La Molina</p>
                </div>
            </div>
        </div>
    @endif
</div>
