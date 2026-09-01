@php
    $config = match ($estado) {
        'exitoso' => [
            'icono' => 'fa-circle-check',
            'color' => 'text-emerald-600',
            'fondo' => 'bg-emerald-50 border-emerald-100',
            'titulo' => '¡Pago exitoso!',
            'descripcion' => 'Tu reserva quedó confirmada. Recibirás el comprobante en tu correo.',
        ],
        'denegado' => [
            'icono' => 'fa-circle-xmark',
            'color' => 'text-red-600',
            'fondo' => 'bg-red-50 border-red-100',
            'titulo' => 'Pago denegado',
            'descripcion' => $mensaje !== ''
                ? $mensaje
                : 'La operación no fue autorizada. Puedes intentar nuevamente con otro medio de pago.',
        ],
        'error' => [
            'icono' => 'fa-triangle-exclamation',
            'color' => 'text-amber-600',
            'fondo' => 'bg-amber-50 border-amber-100',
            'titulo' => 'No se pudo completar el pago',
            'descripcion' => $mensaje !== ''
                ? $mensaje
                : 'Ocurrió un problema al procesar el pago. Intenta de nuevo.',
        ],
        default => [
            'icono' => 'fa-spinner fa-spin',
            'color' => 'text-slate-500',
            'fondo' => 'bg-slate-50 border-slate-200',
            'titulo' => 'Procesando pago',
            'descripcion' => 'Estamos verificando tu operación. Espera un momento…',
        ],
    };

    $urlInicio = $desdePortal ? route('portal.reservar.index') : url('/');
    $urlMisPagos = auth()->check() ? route('mis-pagos.index') : null;
    $urlReintentar = null;

    if ($reserva && in_array($estado, ['denegado', 'error'], true)) {
        $meta = session('pago_meta', []);
        $query = array_filter([
            'sede' => $meta['sede'] ?? $reserva->cancha?->sede_id,
            'cancha_id' => $reserva->cancha_id,
            'fecha' => $reserva->hora_inicio?->format('Y-m-d'),
            'hora' => $reserva->hora_inicio?->format('H:i'),
            'precio' => $reserva->precio_total,
            'reserva_id' => $reserva->id,
        ], fn ($v) => $v !== null && $v !== '');

        $base = $desdePortal ? route('portal.reservar.pago') : route('reservar.pago');
        $urlReintentar = $base.(count($query) ? '?'.http_build_query($query) : '');
    }
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 sm:px-8 pt-8 pb-6 text-center border-b border-slate-100 {{ $config['fondo'] }}">
        <div class="w-16 h-16 mx-auto rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
            <i class="fa-solid {{ $config['icono'] }} text-3xl {{ $config['color'] }}"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $config['titulo'] }}</h1>
        <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto">{{ $config['descripcion'] }}</p>
    </div>

    <div class="p-6 sm:p-8 space-y-5">
        @if ($estado === 'exitoso' && $voucher !== '')
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-800 text-center">
                <span class="font-semibold">Código de voucher:</span>
                <span class="font-mono">{{ $voucher }}</span>
            </div>
        @endif

        @if ($reserva)
            <div class="rounded-xl border border-slate-200 p-4 sm:p-5 space-y-3 text-sm">
                <h2 class="font-bold text-slate-900">Detalle de la reserva</h2>

                @if ($reserva->cancha?->sede)
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Sede</span>
                        <span class="font-medium text-slate-800 text-right">{{ $reserva->cancha->sede->nombre }}</span>
                    </div>
                @endif

                @if ($reserva->cancha)
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Cancha</span>
                        <span class="font-medium text-slate-800 text-right">{{ $reserva->cancha->nombre }}</span>
                    </div>
                @endif

                @if ($reserva->hora_inicio)
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">Fecha y hora</span>
                        <span class="font-medium text-slate-800 text-right">
                            {{ $reserva->hora_inicio->timezone('America/Lima')->translatedFormat('D d/m/Y') }},
                            {{ $reserva->hora_inicio->timezone('America/Lima')->format('H:i') }}
                            @if ($reserva->hora_fin)
                                – {{ $reserva->hora_fin->timezone('America/Lima')->format('H:i') }} hs
                            @endif
                        </span>
                    </div>
                @endif

                <div class="flex justify-between gap-4 pt-2 border-t border-slate-100">
                    <span class="text-slate-500">Total</span>
                    <span class="font-bold text-[#1b5e3b]">S/ {{ number_format((float) $reserva->precio_total, 2) }}</span>
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            @if ($urlReintentar)
                <a href="{{ $urlReintentar }}"
                    class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl bg-[#1b5e3b] hover:bg-[#164d31] text-white text-sm font-semibold transition">
                    <i class="fa-solid fa-rotate-right"></i>
                    Intentar de nuevo
                </a>
            @endif

            @if ($urlMisPagos && $estado === 'exitoso')
                <a href="{{ $urlMisPagos }}"
                    class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
                    <i class="fa-solid fa-receipt"></i>
                    Ver mis pagos
                </a>
            @endif

            <a href="{{ $urlInicio }}"
                class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition {{ ! $urlReintentar && ! ($urlMisPagos && $estado === 'exitoso') ? 'sm:ml-auto' : '' }}">
                <i class="fa-solid fa-house"></i>
                {{ $desdePortal ? 'Volver a reservar' : 'Volver al inicio' }}
            </a>
        </div>
    </div>
</div>
