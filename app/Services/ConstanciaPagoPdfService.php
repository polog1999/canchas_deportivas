<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ConstanciaPagoPdfService
{
    /** @var array<int, float> */
    private const PAPEL_TICKET = [0, 0, 226.772, 841.89];

    public function pagoPorReserva(int $reservaId): ?Pago
    {
        return Pago::query()
            ->with([
                'transaccion.reserva.usuario.perfil',
                'transaccion.reserva.cancha.sede',
                'transaccion.reserva.cancha.deportes',
            ])
            ->whereHas('transaccion', fn ($q) => $q->where('reserva_id', $reservaId))
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array{content: string, filename: string}|null
     */
    public function adjuntoDesdeReserva(Reserva $reserva): ?array
    {
        $pago = $this->pagoPorReserva($reserva->id);

        if (! $pago) {
            return null;
        }

        $datos = $this->datosPdf($pago);
        $contenido = $this->generarContenido($datos);

        return [
            'content' => $contenido,
            'filename' => 'constancia-pago-'.$datos['nro_pedido'].'.pdf',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosPdf(Pago $pago): array
    {
        $pago->loadMissing([
            'transaccion.reserva.usuario.perfil',
            'transaccion.reserva.cancha.sede',
            'transaccion.reserva.cancha.deportes',
        ]);

        $transaccion = $pago->transaccion;
        $reserva = $transaccion?->reserva;
        $titular = $reserva?->usuario;
        $perfil = $titular?->perfil;
        $cancha = $reserva?->cancha;
        $sede = $cancha?->sede;

        $horaInicio = $reserva?->hora_inicio;
        $horaFin = $reserva?->hora_fin;

        $duracionMin = ($horaInicio && $horaFin)
            ? (int) $horaInicio->diffInMinutes($horaFin)
            : 60;

        $deporte = data_get($transaccion?->respuesta_bruta, 'meta.deporte')
            ?? data_get($transaccion?->respuesta_bruta, 'reserva.deporte')
            ?? $cancha?->deportes?->first()?->nombre;

        $monto = round((float) $pago->monto, 2);

        $marca = trim((string) ($transaccion?->marca_tarjeta ?? ''));
        $tarjeta = trim((string) ($transaccion?->tarjeta_enmascarada ?? ''));

        if ($monto <= 0 || strtolower((string) $transaccion?->estado) === 'sin_pasarela') {
            $medioPago = 'Gratuito';
            $estado = 'Gratuito';
        } else {
            $medioPago = ($marca !== '' && $tarjeta !== '')
                ? trim($marca.' '.$tarjeta)
                : ($marca !== '' ? $marca : 'Tarjeta');
            $estado = 'Pagado';
        }

        $concepto = 'Reserva de cancha';
        if ($deporte) {
            $concepto .= ' · '.$deporte;
        }
        $concepto .= ' · '.$duracionMin.' min';
        if ($monto <= 0) {
            $concepto .= ' (cortesía)';
        }

        return [
            'id' => $pago->id,
            'nro_pedido' => (string) ($reserva?->id ?? $pago->id),
            'nro_operacion' => (string) ($transaccion?->transaccion_id ?? '—'),
            'codigo_voucher' => $reserva?->referencia_pago,
            'fecha_pago' => $this->formatearFechaPago($pago),
            'titular' => $titular?->nombreCompleto() ?? '—',
            'dni' => $perfil?->numero_documento ?? '—',
            'sede' => $sede?->nombre ?? '—',
            'cancha' => $cancha?->nombre ?? '—',
            'deporte' => $deporte ?? '—',
            'fecha_turno' => $this->formatearHoraTurno($horaInicio, 'd/m/Y'),
            'horario' => ($horaInicio && $horaFin)
                ? $this->formatearHoraTurno($horaInicio, 'H:i')
                    .' a '
                    .$this->formatearHoraTurno($horaFin, 'H:i')
                    .' hs'
                : '—',
            'concepto' => $concepto,
            'medio_pago' => $medioPago,
            'monto' => $monto,
            'estado' => $estado,
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function generarContenido(array $datos): string
    {
        return Pdf::loadView('pdf.constancia-pago', [
            'pagoSeleccionado' => $datos,
        ])
            ->setPaper(self::PAPEL_TICKET, 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->output();
    }

    private function formatearFechaPago(Pago $pago): string
    {
        $raw = $pago->getRawOriginal('pagado_en');

        if (! $raw) {
            return '—';
        }

        return Carbon::parse($raw, 'UTC')
            ->timezone('America/Lima')
            ->format('d/m/Y H:i');
    }

    private function formatearHoraTurno(?Carbon $fecha, string $formato): string
    {
        if (! $fecha) {
            return '—';
        }

        return $fecha->format($formato);
    }
}
