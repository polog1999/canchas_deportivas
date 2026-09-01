<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 sm:px-8 pt-8 pb-6 text-center border-b border-amber-100 bg-amber-50">
        <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
            <i class="fa-solid fa-triangle-exclamation text-3xl text-amber-600"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">No se pudo completar el pago</h1>
        <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto">
            {{ $comprobante['descripcion_denegacion'] ?? 'Ocurrió un problema al procesar el pago.' }}
        </p>
    </div>

    <div class="p-6 sm:p-8 space-y-5">
        @if (! empty($comprobante['numero_pedido']))
            <dl class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                    <dt class="text-slate-500 font-medium">Número de pedido</dt>
                    <dd class="sm:col-span-2 font-semibold text-slate-900">{{ $comprobante['numero_pedido'] }}</dd>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                    <dt class="text-slate-500 font-medium">Fecha y hora del pedido</dt>
                    <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['fecha_pedido_label'] ?? '—' }}</dd>
                </div>
            </dl>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            @if ($urlReintentar)
                <a href="{{ $urlReintentar }}"
                    class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white text-sm font-semibold transition">
                    <i class="fa-solid fa-rotate-right"></i>
                    Intentar de nuevo
                </a>
            @endif

            <a href="{{ $urlInicio }}"
                class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition {{ $urlReintentar ? '' : 'sm:ml-auto' }}">
                <i class="fa-solid fa-house"></i>
                Volver al inicio
            </a>
        </div>
    </div>
</div>
