<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reprogramacion;
use App\Models\Reserva;
use App\Support\PagoPdfToken;
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

        return $this->adjuntoDesdePago($pago);
    }

    /**
     * @return array{content: string, filename: string}|null
     */
    public function adjuntoDesdePago(Pago $pago): ?array
    {
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
            $medioPago = $this->formatearMedioPago($marca, $tarjeta);
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
            'tipo' => 'pago',
            'clave' => PagoPdfToken::claveDePago($pago->id),
            'orden' => $this->marcaDeTiempo($pago->getRawOriginal('pagado_en')),
            'pdf_token' => PagoPdfToken::generar(PagoPdfToken::claveDePago($pago->id)),
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
     * Constancia de un cambio de horario.
     *
     * Reutiliza los datos del pago original (es el mismo dinero) y reemplaza
     * el turno por el nuevo, marcando el comprobante como reprogramado.
     *
     * @return array<string, mixed>
     */
    public function datosPdfReprogramacion(Reprogramacion $reprogramacion): array
    {
        $reprogramacion->loadMissing([
            'reserva.usuario.perfil',
            'canchaAnterior',
            'canchaNueva.sede',
            'canchaNueva.deportes',
        ]);

        $reserva = $reprogramacion->reserva;
        $pago = $reserva ? $this->pagoPorReserva($reserva->id) : null;
        $base = $pago ? $this->datosPdf($pago) : [];

        $canchaNueva = $reprogramacion->canchaNueva;
        $inicio = $reprogramacion->hora_inicio_nueva;
        $fin = $reprogramacion->hora_fin_nueva;
        $duracionMin = (int) $inicio->diffInMinutes($fin);

        $deporte = $base['deporte']
            ?? $canchaNueva?->deportes?->first()?->nombre
            ?? '—';

        $concepto = 'Reserva de cancha';
        if ($deporte !== '—') {
            $concepto .= ' · '.$deporte;
        }
        $concepto .= ' · '.$duracionMin.' min (reprogramada)';

        return array_merge($base, [
            'id' => $reprogramacion->id,
            'tipo' => 'reprogramacion',
            'clave' => PagoPdfToken::claveDeReprogramacion($reprogramacion->id),
            'orden' => $this->marcaDeTiempo($reprogramacion->getRawOriginal('creado_en')),
            'pdf_token' => PagoPdfToken::generar(
                PagoPdfToken::claveDeReprogramacion($reprogramacion->id)
            ),
            'estado' => 'Reprogramado',
            'nro_reprogramacion' => 'REP-'.str_pad((string) $reprogramacion->id, 4, '0', STR_PAD_LEFT),
            'fecha_pago' => $reprogramacion->creado_en?->format('d/m/Y H:i') ?? '—',
            'sede' => $canchaNueva?->sede?->nombre ?? ($base['sede'] ?? '—'),
            'cancha' => $canchaNueva?->nombre ?? '—',
            'deporte' => $deporte,
            'fecha_turno' => $inicio->format('d/m/Y'),
            'horario' => $inicio->format('H:i').' a '.$fin->format('H:i').' hs',
            'concepto' => $concepto,
            'monto' => round((float) $reprogramacion->monto_validado, 2),
            'motivo' => $reprogramacion->motivo,
            'cancha_anterior' => $reprogramacion->canchaAnterior?->nombre ?? '—',
            'turno_anterior' => $reprogramacion->hora_inicio_anterior->format('d/m/Y H:i')
                .' a '.$reprogramacion->hora_fin_anterior->format('H:i').' hs',
        ]);
    }

    /**
     * @return array{content: string, filename: string}
     */
    public function adjuntoDesdeReprogramacion(Reprogramacion $reprogramacion): array
    {
        $datos = $this->datosPdfReprogramacion($reprogramacion);

        return [
            'content' => $this->generarContenido($datos),
            'filename' => 'constancia-reprogramacion-'.$datos['nro_reprogramacion'].'.pdf',
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

    /**
     * Marca usada solo para ordenar comprobantes de distinto tipo.
     */
    private function marcaDeTiempo(mixed $raw): int
    {
        return $raw ? Carbon::parse($raw)->timestamp : 0;
    }

    private function formatearFechaPago(Pago $pago): string
    {
        $raw = $pago->getRawOriginal('pagado_en');

        if (! $raw) {
            return '—';
        }

        return Carbon::parse($raw)
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

    private function formatearMedioPago(string $marca, string $tarjeta): string
    {
        $tarjetaEnmascarada = self::enmascararTarjeta($tarjeta);

        if ($tarjetaEnmascarada === '') {
            return $marca !== '' ? ucfirst(strtolower($marca)) : 'Tarjeta';
        }

        $marcaLabel = $marca !== '' ? ucfirst(strtolower($marca)).' ' : '';

        return trim($marcaLabel.$tarjetaEnmascarada);
    }

    public static function enmascararTarjeta(?string $tarjeta): string
    {
        $tarjeta = trim((string) $tarjeta);

        if ($tarjeta === '') {
            return '';
        }

        $digitos = preg_replace('/\D+/', '', $tarjeta);

        if ($digitos === null || strlen($digitos) < 4) {
            return '****';
        }

        return '**** **** **** '.substr($digitos, -4);
    }
}
