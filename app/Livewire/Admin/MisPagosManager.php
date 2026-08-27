<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

class MisPagosManager extends Component
{
    public string $search = '';

    public bool $mostrarVoucher = false;

    /** @var array<string, mixed>|null */
    public ?array $pagoSeleccionado = null;

    /**
     * Maqueta: pagos ficticios adaptados a reservas de canchas.
     *
     * @return list<array<string, mixed>>
     */
    private function pagosFicticios(): array
    {
        return [
            [
                'id' => 57,
                'nro_pedido' => '57',
                'nro_operacion' => 'NIUB-88421',
                'codigo_voucher' => 'VCH-00057',
                'fecha_pago' => '27/08/2026 16:44',
                'titular' => 'Marco Junior Medina',
                'dni' => '70829122',
                'sede' => 'Old Trafford',
                'cancha' => 'Cancha Neo 2',
                'deporte' => 'Fútbol 11',
                'fecha_turno' => '29/08/2026',
                'horario' => '12:00 a 13:00 hs',
                'concepto' => 'Reserva de cancha · Fútbol 11 · 60 min',
                'medio_pago' => 'Tarjeta Visa **** 4521',
                'monto' => 80.00,
                'estado' => 'Pagado',
            ],
            [
                'id' => 58,
                'nro_pedido' => '58',
                'nro_operacion' => 'NIUB-88455',
                'codigo_voucher' => 'VCH-00058',
                'fecha_pago' => '26/08/2026 10:12',
                'titular' => 'Ana Lucía Quispe',
                'dni' => '45678123',
                'sede' => 'Camp Nou Molina',
                'cancha' => 'Cancha 1',
                'deporte' => 'Vóley',
                'fecha_turno' => '28/08/2026',
                'horario' => '18:00 a 19:00 hs',
                'concepto' => 'Reserva de cancha · Vóley · 60 min',
                'medio_pago' => 'Yape / Plin',
                'monto' => 45.00,
                'estado' => 'Pagado',
            ],
            [
                'id' => 59,
                'nro_pedido' => '59',
                'nro_operacion' => 'GRATUITO',
                'codigo_voucher' => 'VCH-00059',
                'fecha_pago' => '25/08/2026 09:30',
                'titular' => 'Carlos Eduardo Ríos',
                'dni' => '70112233',
                'sede' => 'San Francisco',
                'cancha' => 'Cancha sintética A',
                'deporte' => 'Fútbol 7',
                'fecha_turno' => '27/08/2026',
                'horario' => '07:00 a 08:00 hs',
                'concepto' => 'Reserva de cancha · Fútbol 7 · 60 min (cortesía)',
                'medio_pago' => 'Gratuito',
                'monto' => 0.00,
                'estado' => 'Gratuito',
            ],
            [
                'id' => 60,
                'nro_pedido' => '60',
                'nro_operacion' => 'NIUB-88502',
                'codigo_voucher' => 'VCH-00060',
                'fecha_pago' => '24/08/2026 20:05',
                'titular' => 'María Fernanda Soto',
                'dni' => '77889900',
                'sede' => 'Old Trafford',
                'cancha' => 'Cancha 1',
                'deporte' => 'Fútbol 11',
                'fecha_turno' => '30/08/2026',
                'horario' => '20:00 a 22:00 hs',
                'concepto' => 'Reserva de cancha · Fútbol 11 · 120 min',
                'medio_pago' => 'Tarjeta Mastercard **** 1190',
                'monto' => 160.00,
                'estado' => 'Pagado',
            ],
        ];
    }

    public function verVoucher(int $id): void
    {
        $pago = collect($this->pagosFicticios())->firstWhere('id', $id);
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

        $pagos = collect($this->pagosFicticios())
            ->when($q !== '', function ($coleccion) use ($q) {
                return $coleccion->filter(function (array $p) use ($q) {
                    $haystack = mb_strtolower(implode(' ', [
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
                    ]));

                    return str_contains($haystack, $q);
                });
            })
            ->values();

        return view('livewire.admin.mis-pagos', [
            'pagos' => $pagos,
        ]);
    }
}
