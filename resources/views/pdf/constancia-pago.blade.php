<div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:keydown.escape.window="cerrarVoucher">
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
                    <p class="mt-3 font-bold text-xs uppercase tracking-wider text-[#1b5e3b]">Reserva de canchas
                        deportivas</p>
                    <p class="text-[11px] text-gray-400">molicanchas.munimolina.gob.pe</p>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>

                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">N° PEDIDO:</span>
                            <strong>{{ $pagoSeleccionado['nro_pedido'] }}</strong></p>
                        <p><span class="text-gray-500">CÓDIGO VOUCHER:</span>
                            <strong>{{ $pagoSeleccionado['codigo_voucher'] ?? '—' }}</strong></p>
                        <p><span class="text-gray-500">N° OPERACIÓN:</span>
                            <strong>{{ $pagoSeleccionado['nro_operacion'] }}</strong></p>
                        <p><span class="text-gray-500">FECHA Y HORA DEL PAGO:</span>
                            {{ $pagoSeleccionado['fecha_pago'] }} <span class="text-gray-400">(hora Perú)</span></p>
                    </div>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 text-left mb-2">Pagado por
                    </p>
                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">NOMBRE:</span>
                            <strong>{{ $pagoSeleccionado['titular'] }}</strong></p>
                        <p><span class="text-gray-500">DNI:</span> {{ $pagoSeleccionado['dni'] }}</p>
                    </div>

                    <div class="my-4 border-t border-dashed border-gray-300"></div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 text-left mb-2">Detalle</p>
                    <div class="text-left space-y-1 text-[12px]">
                        <p><span class="text-gray-500">CONCEPTO:</span> {{ $pagoSeleccionado['concepto'] }}</p>
                        <p><span class="text-gray-500">SEDE:</span> {{ $pagoSeleccionado['sede'] }}</p>
                        <p><span class="text-gray-500">CANCHA:</span> {{ $pagoSeleccionado['cancha'] }}</p>
                        <p><span class="text-gray-500">DEPORTE:</span> {{ $pagoSeleccionado['deporte'] }}</p>
                        <p><span class="text-gray-500">TURNO RESERVADO:</span> {{ $pagoSeleccionado['fecha_turno'] }} ·
                            {{ $pagoSeleccionado['horario'] }}</p>
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