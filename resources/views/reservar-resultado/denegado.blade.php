<div id="comprobante-pago" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 sm:px-8 pt-8 pb-6 text-center border-b border-red-100 bg-red-50">
        <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
            <i class="fa-solid fa-circle-xmark text-3xl text-red-600"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Transacción denegada</h1>
        <p class="text-sm text-slate-600 mt-2">No se pudo autorizar el pago.</p>
    </div>

    <div class="p-6 sm:p-8 space-y-5">
        <dl class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Número de pedido</dt>
                <dd class="sm:col-span-2 font-semibold text-slate-900">{{ $comprobante['numero_pedido'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Fecha y hora del pedido</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $comprobante['fecha_pedido_label'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4 px-4 py-3">
                <dt class="text-slate-500 font-medium">Descripción de la denegación</dt>
                <dd class="sm:col-span-2 text-red-700 font-medium">{{ $comprobante['descripcion_denegacion'] ?? 'La transacción fue denegada.' }}</dd>
            </div>
        </dl>

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
