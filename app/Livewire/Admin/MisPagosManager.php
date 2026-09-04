<?php

namespace App\Livewire\Admin;

use App\Models\Pago;
use App\Models\Reprogramacion;
use App\Models\Usuario;
use App\Services\ConstanciaPagoPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MisPagosManager extends Component
{
    public string $search = '';

    public bool $mostrarVoucher = false;

    /** @var array<string, mixed>|null */
    public ?array $pagoSeleccionado = null;

    /**
     * Constancias de pago y de reprogramación, ordenadas de la más reciente
     * a la más antigua. Una reserva reprogramada tiene ambas.
     *
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
        $pdf = app(ConstanciaPagoPdfService::class);

        $alcance = function ($q) use ($usuario, $esAdmin) {
            $q->whereRaw('LOWER(estado) = ?', ['confirmada']);

            if (! $esAdmin) {
                $q->where('usuario_id', $usuario->id);
            }
        };

        $pagos = Pago::query()
            ->with([
                'transaccion.reserva.usuario.perfil',
                'transaccion.reserva.cancha.sede',
                'transaccion.reserva.cancha.deportes',
            ])
            ->whereHas('transaccion.reserva', $alcance)
            ->get()
            ->map(fn (Pago $pago) => $pdf->datosPdf($pago));

        $reprogramaciones = Reprogramacion::query()
            ->with([
                'reserva.usuario.perfil',
                'canchaAnterior',
                'canchaNueva.sede',
                'canchaNueva.deportes',
            ])
            ->whereHas('reserva', $alcance)
            ->get()
            ->map(fn (Reprogramacion $r) => $pdf->datosPdfReprogramacion($r));

        return $pagos->concat($reprogramaciones)
            ->sortByDesc('orden')
            ->values();
    }

    public function verVoucher(string $clave): void
    {
        $pago = $this->pagosReales()->firstWhere('clave', $clave);

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

    /**
     * Método utilizado por el controlador del PDF.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function pagosParaPdf(): Collection
    {
        return $this->pagosReales();
    }

    /**
     * Descarga directa del PDF.
     */
    public function descargarPdf(string $clave)
    {
        $pago = $this->pagosReales()->firstWhere('clave', $clave);

        if (! $pago) {
            abort(404, 'Comprobante no encontrado.');
        }

        $customPaper = [0, 0, 226.772, 841.89];

        $pdf = Pdf::loadView('pdf.constancia-pago', [
            'pagoSeleccionado' => $pago,
        ])
            ->setPaper($customPaper, 'portrait')
            ->setOption('isRemoteEnabled', true);

        $nombre = ($pago['tipo'] ?? 'pago') === 'reprogramacion'
            ? 'constancia-reprogramacion-'.$pago['nro_reprogramacion']
            : 'constancia-pago-'.$pago['nro_pedido'];

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $nombre.'.pdf'
        );
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        $q = mb_strtolower(trim($this->search));

        $pagos = $this->pagosReales()
            ->when($q !== '', function (Collection $coleccion) use ($q) {

                return $coleccion->filter(function (array $p) use ($q) {

                    $haystack = mb_strtolower(
                        implode(
                            ' ',
                            array_filter([
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
                                $p['nro_reprogramacion'] ?? '',
                            ])
                        )
                    );

                    return str_contains($haystack, $q);
                });
            })
            ->values();

        return view('livewire.admin.mis-pagos', [
            'pagos' => $pagos,
        ]);
    }
}