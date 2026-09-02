@php
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
    } elseif (! $reserva && in_array($estado, ['denegado', 'error'], true)) {
        $returnQuery = trim((string) session('pago_return_query', ''));
        if ($returnQuery !== '') {
            $base = $desdePortal ? route('portal.reservar.pago') : route('reservar.pago');
            $urlReintentar = $base.'?'.ltrim($returnQuery, '?');
        }
    }
@endphp

@if ($estado === 'exitoso')
    @include('reservar-resultado.exitoso', compact('comprobante', 'urlInicio', 'urlMisPagos'))
@elseif ($estado === 'denegado')
    @include('reservar-resultado.denegado', compact('comprobante', 'urlInicio', 'urlReintentar'))
@elseif ($estado === 'error')
    @include('reservar-resultado.error', compact('comprobante', 'urlInicio', 'urlReintentar'))
@else
    @include('reservar-resultado.procesando', compact('urlInicio'))
@endif
