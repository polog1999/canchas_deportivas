<div class="p-6 bg-gray-50 min-h-screen">

    <x-slot name="title">
        Mis Pagos
    </x-slot>


    <div class="max-w-7xl mx-auto">

        {{-- ==========================================================
             ENCABEZADO
             ========================================================== --}}

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">

            <div>

                <h2 class="text-2xl font-bold text-gray-800">
                    Mis Pagos
                </h2>

                <p class="text-sm text-gray-600">
                    Constancia de pagos por reservas de canchas.
                </p>

            </div>

        </div>


        {{-- ==========================================================
             BUSCADOR
             ========================================================== --}}

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">

            <div class="relative w-full sm:w-80">

                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                    <i class="fa-solid fa-magnifying-glass"></i>

                </span>

                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por voucher, DNI, sede, cancha..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                />

            </div>

        </div>


        {{-- ==========================================================
             TABLA
             ========================================================== --}}

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full text-left border-collapse">

                    <thead>

                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-100">

                            <th class="py-4 px-6">
                                Pedido
                            </th>

                            <th class="py-4 px-6">
                                Fecha de pago
                            </th>

                            <th class="py-4 px-6">
                                Titular
                            </th>

                            <th class="py-4 px-6">
                                Reserva
                            </th>

                            <th class="py-4 px-6">
                                Monto
                            </th>

                            <th class="py-4 px-6">
                                Estado
                            </th>

                            <th class="py-4 px-6 text-center">
                                Voucher
                            </th>

                        </tr>

                    </thead>


                    <tbody class="text-gray-700 text-sm divide-y divide-gray-100">

                        @forelse ($pagos as $pago)

                            <tr class="hover:bg-gray-50/70 transition-colors">

                                {{-- PEDIDO --}}
                                <td class="py-4 px-6">

                                    <div class="font-bold text-gray-900">

                                        #{{ $pago['nro_pedido'] }}

                                    </div>

                                    <div class="text-[11px] text-gray-400">

                                        {{ $pago['codigo_voucher'] ?? $pago['nro_operacion'] }}

                                    </div>

                                </td>


                                {{-- FECHA --}}
                                <td class="py-4 px-6 whitespace-nowrap">

                                    {{ $pago['fecha_pago'] }}

                                </td>


                                {{-- TITULAR --}}
                                <td class="py-4 px-6">

                                    <div class="font-medium text-gray-900">

                                        {{ $pago['titular'] }}

                                    </div>

                                    <div class="text-[11px] text-gray-400">

                                        DNI {{ $pago['dni'] }}

                                    </div>

                                </td>


                                {{-- RESERVA --}}
                                <td class="py-4 px-6">

                                    <div class="font-medium text-gray-900">

                                        {{ $pago['sede'] }} · {{ $pago['cancha'] }}

                                    </div>

                                    <div class="text-[11px] text-gray-500">

                                        {{ $pago['deporte'] }}
                                        ·
                                        {{ $pago['fecha_turno'] }}
                                        ·
                                        {{ $pago['horario'] }}

                                    </div>

                                </td>


                                {{-- MONTO --}}
                                <td class="py-4 px-6 font-semibold whitespace-nowrap">

                                    S/
                                    {{ number_format($pago['monto'], 2) }}

                                </td>


                                {{-- ESTADO --}}
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


                                {{-- ACCIONES --}}
                                <td class="py-4 px-6 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        {{-- VER PDF --}}
                                        <button
                                            type="button"
                                            wire:click="verVoucher({{ $pago['id'] }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition"
                                        >

                                            <i class="fa-solid fa-file-pdf"></i>

                                            Ver

                                        </button>


                                        {{-- DESCARGAR PDF --}}
                                        {{-- <a
                                            href="{{ route('mis-pagos.pdf', $pago['id']) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold transition"
                                        >

                                            <i class="fa-solid fa-download"></i>

                                            PDF

                                        </a> --}}

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="py-10 px-6 text-center text-gray-500"
                                >

                                    No hay pagos para mostrar.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ==============================================================
         VISOR PDF
         ============================================================== --}}

    @if ($mostrarVoucher && $pagoSeleccionado)

        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            wire:keydown.escape.window="cerrarVoucher"
        >

            {{-- FONDO OSCURO --}}
            <div
                class="absolute inset-0 bg-black/60"
                wire:click="cerrarVoucher"
            ></div>


            {{-- CONTENEDOR --}}
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden flex flex-col"
            >

                {{-- ==================================================
                     HEADER DEL VISOR
                     ================================================== --}}

                <div
                    class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-gray-50 flex-shrink-0"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center"
                        >

                            <i class="fa-solid fa-file-pdf text-lg"></i>

                        </div>


                        <div>

                            <h3 class="text-sm font-bold text-gray-800">

                                Constancia de pago

                            </h3>

                            <p class="text-xs text-gray-500">

                                Pedido #{{ $pagoSeleccionado['nro_pedido'] }}

                            </p>

                        </div>

                    </div>


                    <div class="flex items-center gap-2">

                        {{-- DESCARGAR --}}
                        <a
                            href="{{ route('mis-pagos.pdf', $pagoSeleccionado['id']) }}"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold transition"
                        >

                            <i class="fa-solid fa-download"></i>

                            Descargar

                        </a>


                        {{-- CERRAR --}}
                        <button
                            type="button"
                            wire:click="cerrarVoucher"
                            class="w-9 h-9 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 flex items-center justify-center transition"
                        >

                            <i class="fa-solid fa-xmark text-lg"></i>

                        </button>

                    </div>

                </div>


                {{-- ==================================================
                     VISOR PDF
                     ================================================== --}}

                <div class="flex-1 bg-gray-300 overflow-hidden">

                    <iframe
                        src="{{ route('mis-pagos.pdf', $pagoSeleccionado['id']) }}"
                        class="w-full h-full border-0"
                        title="Constancia de pago PDF"
                    ></iframe>

                </div>

            </div>

        </div>

    @endif

</div>