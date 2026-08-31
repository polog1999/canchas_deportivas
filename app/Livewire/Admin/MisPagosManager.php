<?php

namespace App\Livewire\Admin;

use App\Models\Pago;
use App\Models\Usuario;
use App\Services\ReservaVoucherService;
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
        $voucherService = app(ReservaVoucherService::class);

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
            ->map(fn (Pago $pago) => $voucherService->datosDesdePago($pago))
            ->values();
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
}
