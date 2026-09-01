<div id="comprobante-pago" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-slate-300">
    <div class="px-6 sm:px-8 pt-8 pb-6 text-center border-b border-emerald-100 bg-emerald-50 print:bg-white">
        <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center mb-4 print:shadow-none">
            <i class="fa-solid fa-circle-check text-3xl text-emerald-600"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Transacción autorizada</h1>
        <p class="text-sm text-slate-600 mt-2">Su pago fue procesado correctamente.</p>
    </div>

    <div class="p-6 sm:p-8 space-y-5">
        <dl class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Número de pedido</dt>
                <dd class="sm:col-span-2 font-semibold text-slate-900">{{ $comprobante['numero_pedido'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Nombre y apellido del tarjetahabiente</dt>
                <dd class="sm:col-span-2 font-semibold text-slate-900">{{ $comprobante['titular'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Fecha y hora del pedido</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['fecha_pedido_label'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Importe de la transacción</dt>
                <dd class="sm:col-span-2 font-bold text-[#1b5e3b] text-lg">{{ $comprobante['importe_label'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Tipo de moneda</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['moneda_label'] ?? 'Soles (PEN)' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Descripción del producto</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['descripcion_producto'] ?? '—' }}</dd>
            </div>
            @if (! empty($comprobante['voucher']))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                    <dt class="text-slate-500 font-medium">Código de voucher</dt>
                    <dd class="sm:col-span-2 font-mono text-slate-900">{{ $comprobante['voucher'] }}</dd>
                </div>
            @endif
            @if (! empty($comprobante['codigo_autorizacion']))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                    <dt class="text-slate-500 font-medium">Código de autorización</dt>
                    <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['codigo_autorizacion'] }}</dd>
                </div>
            @endif
            @if (! empty($comprobante['tarjeta_enmascarada']))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                    <dt class="text-slate-500 font-medium">Tarjeta</dt>
                    <dd class="sm:col-span-2 text-slate-900">
                        {{ $comprobante['marca_tarjeta'] ? $comprobante['marca_tarjeta'].' ' : '' }}{{ $comprobante['tarjeta_enmascarada'] }}
                    </dd>
                </div>
            @endif
        </dl>

        <div class="rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">
            <p class="font-semibold text-slate-800 mb-2">Términos y condiciones</p>
            <p class="mb-2">
                La reserva es personal, está sujeta a disponibilidad y se confirma tras la acreditación del pago.
                Debe presentar documento de identidad al usar la cancha. Los pagos vía Niubiz están sujetos a
                validación antifraude.
            </p>
            <p class="text-xs text-slate-400">Municipalidad de La Molina — Servicio de reserva de canchas deportivas.</p>
        </div>

        <p class="text-xs text-slate-500 text-center print:hidden">
            Le recomendamos imprimir o guardar esta información como comprobante de su operación.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 pt-2 print:hidden">
            <button type="button" onclick="window.print()"
                class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white text-sm font-semibold transition">
                <i class="fa-solid fa-print"></i>
                Imprimir comprobante
            </button>

            @if ($urlMisPagos)
                <a href="{{ $urlMisPagos }}"
                    class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
                    <i class="fa-solid fa-receipt"></i>
                    Ver mis pagos
                </a>
            @endif

            <a href="{{ $urlInicio }}"
                class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition sm:ml-auto">
                <i class="fa-solid fa-house"></i>
                Volver al inicio
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #comprobante-pago, #comprobante-pago * { visibility: visible; }
        #comprobante-pago { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>
