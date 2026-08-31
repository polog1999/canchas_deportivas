<?php

namespace App\Livewire\Admin;

use App\Models\Pago;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class MisPagosManager extends Component
{
    public string $search = '';

    public bool $mostrarVoucher = false;

    /** @var array<string, mixed>|null */
    public ?array $pagoSeleccionado = null;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function pagosReales(): Collection
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            return collect();
        }

        $esAdmin = $usuario->tieneRol('admin', 'ADMIN', 'SUPERADMIN');

        return Pago::query()
            ->with([
                'transaccion.reserva.usuario.perfil',
                'transaccion.reserva.cancha.sede',
                'transaccion.reserva.cancha.deportes',
            ])
            ->whereHas('transaccion.reserva', function ($q) use ($usuario, $esAdmin) {
                $q->whereRaw('LOWER(estado) = ?', ['confirmada']);

                if (! $esAdmin) {
                    $q->where('usuario_id', $usuario->id);
                }
            })
            ->orderByDesc('pagado_en')
            ->get()
            ->map(fn (Pago $pago) => $this->mapearPago($pago))
            ->values();
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

    /**
     * @return array<string, mixed>
     */
    private function mapearPago(Pago $pago): array
    {
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
                ? $this->formatearHoraTurno($horaInicio, 'H:i').' a '.$this->formatearHoraTurno($horaFin, 'H:i').' hs'
                : '—',
            'concepto' => $concepto,
            'medio_pago' => $medioPago,
            'monto' => $monto,
            'estado' => $estado,
        ];
    }

    public function verVoucher(int $id): void
    {
        $pago = $this->pagosReales()->firstWhere('id', $id);
        if (! $pago) {
            return;
        }

        $this->pagoSeleccionado = $pago;
        $this->mostrarVoucher = true;
    }

    public function cerrarVoucher(): void
    {
        $this->mostrarVoucher = false;
        $this->pagoSeleccionado = null;
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        $q = mb_strtolower(trim($this->search));

        $pagos = $this->pagosReales()
            ->when($q !== '', function (Collection $coleccion) use ($q) {
                return $coleccion->filter(function (array $p) use ($q) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $p['nro_pedido'],
                        $p['nro_operacion'],
                        $p['codigo_voucher'] ?? '',
                        $p['titular'],
                        $p['dni'],
                        $p['sede'],
                        $p['cancha'],
                        $p['deporte'],
                        $p['concepto'],
                        $p['estado'],
                    ])));

                    return str_contains($haystack, $q);
                });
            })
            ->values();

        return view('livewire.admin.mis-pagos', [
            'pagos' => $pagos,
        ]);
    }
    public function descargarPdf(int $id)
{
    $pago = $this->pagosReales()->firstWhere('id', $id);

    if (! $pago) {
        abort(404, 'Pago no encontrado.');
    }
// Tu lógica de generación de PDF (sin cambios)
        $customPaper = [0, 0, 226.772, 841.89];
    $pdf = Pdf::loadView('pdf.constancia-pago', [
        'pagoSeleccionado' => $pago,
        // 'pagoSeleccionado' => $this->pagoSeleccionado,
    ]) ->setPaper($customPaper, 'portrait')
            ->setOption('isRemoteEnabled', true);

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'constancia-pago-' . $pago['nro_pedido'] . '.pdf'
    );
}
}
