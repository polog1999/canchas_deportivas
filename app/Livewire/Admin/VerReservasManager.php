<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class VerReservasManager extends Component
{
    public string $search = '';

    public string $filtroEstado = '';

    public int $mes;

    public int $anio;

    public ?string $fechaSeleccionada = null;

    public function mount(): void
    {
        // Mes de la maqueta (datos ficticios en ago–sep 2026)
        $this->mes = 8;
        $this->anio = 2026;
    }

    public function mesAnterior(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->subMonth();
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
        $this->fechaSeleccionada = null;
    }

    public function mesSiguiente(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->addMonth();
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
        $this->fechaSeleccionada = null;
    }

    public function irHoy(): void
    {
        $hoy = Carbon::now();
        $this->mes = (int) $hoy->month;
        $this->anio = (int) $hoy->year;
        $this->fechaSeleccionada = $hoy->toDateString();
    }

    public function updatedSearch(): void
    {
        $q = mb_strtolower(trim($this->search));
        if ($q === '') {
            return;
        }

        // Si busca un voucher/reserva exacto, saltar al mes y día correspondiente
        $match = $this->reservasFiltradas()->first(function (array $r) use ($q) {
            $codigos = array_filter([
                mb_strtolower((string) ($r['codigo'] ?? '')),
                mb_strtolower((string) ($r['codigo_voucher'] ?? '')),
                mb_strtolower((string) ($r['referencia'] ?? '')),
            ]);

            return in_array($q, $codigos, true)
                || collect($codigos)->contains(fn ($c) => str_contains($c, $q));
        });

        if (! $match) {
            return;
        }

        $fecha = \Carbon\Carbon::parse($match['fecha']);
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
        $this->fechaSeleccionada = $fecha->toDateString();
    }

    public function seleccionarDia(string $fecha): void
    {
        $this->fechaSeleccionada = $fecha;
    }

    /**
     * Maqueta: reservas pagadas (nivel reserva).
     *
     * @return list<array<string, mixed>>
     */
    private function reservasFicticias(): array
    {
        return [
            [
                'id' => 101,
                'codigo' => 'RES-0101',
                'titular' => 'Marco Junior Medina',
                'dni' => '70829122',
                'sede' => 'Old Trafford',
                'direccion' => 'Molina',
                'cancha' => 'Cancha Neo 2',
                'deporte' => 'Fútbol 11',
                'fecha' => '2026-08-29',
                'fecha_label' => 'sáb 29/08/2026',
                'horario' => '12:00 — 13:00',
                'duracion' => '60 min',
                'precio' => 80.00,
                'estado' => 'Confirmada',
                'pago' => 'Pagado',
                'referencia' => 'NIUB-88421',
                'codigo_voucher' => 'VCH-00057',
                'imagen' => asset('imagenes/sedes/old.jpg'),
            ],
            [
                'id' => 102,
                'codigo' => 'RES-0102',
                'titular' => 'Ana Lucía Quispe',
                'dni' => '45678123',
                'sede' => 'Camp Nou Molina',
                'direccion' => 'La Molina',
                'cancha' => 'Cancha 1',
                'deporte' => 'Vóley',
                'fecha' => '2026-08-28',
                'fecha_label' => 'vie 28/08/2026',
                'horario' => '18:00 — 19:00',
                'duracion' => '60 min',
                'precio' => 45.00,
                'estado' => 'Confirmada',
                'pago' => 'Pagado',
                'referencia' => 'NIUB-88455',
                'codigo_voucher' => 'VCH-00058',
                'imagen' => asset('imagenes/sedes/camp.png'),
            ],
            [
                'id' => 103,
                'codigo' => 'RES-0103',
                'titular' => 'Carlos Eduardo Ríos',
                'dni' => '70112233',
                'sede' => 'San Francisco',
                'direccion' => 'La Molina',
                'cancha' => 'Cancha sintética A',
                'deporte' => 'Fútbol 7',
                'fecha' => '2026-08-27',
                'fecha_label' => 'jue 27/08/2026',
                'horario' => '07:00 — 08:00',
                'duracion' => '60 min',
                'precio' => 0.00,
                'estado' => 'Confirmada',
                'pago' => 'Gratuito',
                'referencia' => 'GRATUITO',
                'codigo_voucher' => 'VCH-00059',
                'imagen' => asset('imagenes/sedes/san.png'),
            ],
            [
                'id' => 104,
                'codigo' => 'RES-0104',
                'titular' => 'María Fernanda Soto',
                'dni' => '77889900',
                'sede' => 'Old Trafford',
                'direccion' => 'Molina',
                'cancha' => 'Cancha 1',
                'deporte' => 'Fútbol 11',
                'fecha' => '2026-08-30',
                'fecha_label' => 'dom 30/08/2026',
                'horario' => '20:00 — 22:00',
                'duracion' => '120 min',
                'precio' => 160.00,
                'estado' => 'Confirmada',
                'pago' => 'Pagado',
                'referencia' => 'NIUB-88502',
                'codigo_voucher' => 'VCH-00060',
                'imagen' => asset('imagenes/sedes/old.jpg'),
            ],
            [
                'id' => 107,
                'codigo' => 'RES-0107',
                'titular' => 'Jorge Luis Paredes',
                'dni' => '73445566',
                'sede' => 'Old Trafford',
                'direccion' => 'Molina',
                'cancha' => 'Cancha Neo 2',
                'deporte' => 'Fútbol 11',
                'fecha' => '2026-08-27',
                'fecha_label' => 'jue 27/08/2026',
                'horario' => '19:00 — 20:00',
                'duracion' => '60 min',
                'precio' => 80.00,
                'estado' => 'Confirmada',
                'pago' => 'Pagado',
                'referencia' => 'NIUB-88490',
                'codigo_voucher' => 'VCH-00061',
                'imagen' => asset('imagenes/sedes/old.jpg'),
            ],
            [
                'id' => 105,
                'codigo' => 'RES-0105',
                'titular' => 'Luis Alberto Vargas',
                'dni' => '41234567',
                'sede' => 'Camp Nou Molina',
                'direccion' => 'La Molina',
                'cancha' => 'Cancha 2',
                'deporte' => 'Básquet',
                'fecha' => '2026-08-26',
                'fecha_label' => 'mié 26/08/2026',
                'horario' => '16:00 — 17:00',
                'duracion' => '60 min',
                'precio' => 50.00,
                'estado' => 'Cancelada',
                'pago' => 'Reembolsado',
                'referencia' => 'NIUB-88390',
                'codigo_voucher' => 'VCH-00055',
                'imagen' => asset('imagenes/sedes/camp.png'),
            ],
            [
                'id' => 106,
                'codigo' => 'RES-0106',
                'titular' => 'Patricia Gómez Huamán',
                'dni' => '72334455',
                'sede' => 'San Francisco',
                'direccion' => 'La Molina',
                'cancha' => 'Multiuso B',
                'deporte' => 'Tenis',
                'fecha' => '2026-09-01',
                'fecha_label' => 'mar 01/09/2026',
                'horario' => '09:00 — 10:00',
                'duracion' => '60 min',
                'precio' => 55.00,
                'estado' => 'Pendiente',
                'pago' => 'Por pagar',
                'referencia' => '—',
                'codigo_voucher' => null,
                'imagen' => asset('imagenes/sedes/san.png'),
            ],
            [
                'id' => 108,
                'codigo' => 'RES-0108',
                'titular' => 'Rosa María Delgado',
                'dni' => '75667788',
                'sede' => 'Camp Nou Molina',
                'direccion' => 'La Molina',
                'cancha' => 'Cancha 1',
                'deporte' => 'Vóley',
                'fecha' => '2026-08-15',
                'fecha_label' => 'sáb 15/08/2026',
                'horario' => '10:00 — 11:00',
                'duracion' => '60 min',
                'precio' => 45.00,
                'estado' => 'Confirmada',
                'pago' => 'Pagado',
                'referencia' => 'NIUB-88110',
                'codigo_voucher' => 'VCH-00048',
                'imagen' => asset('imagenes/sedes/camp.png'),
            ],
        ];
    }

    private function reservasFiltradas()
    {
        $q = mb_strtolower(trim($this->search));
        $estado = $this->filtroEstado;

        return collect($this->reservasFicticias())
            ->when($estado !== '', fn ($c) => $c->where('estado', $estado))
            ->when($q !== '', function ($coleccion) use ($q) {
                return $coleccion->filter(function (array $r) use ($q) {
                    $haystack = mb_strtolower(implode(' ', [
                        $r['codigo'],
                        $r['codigo_voucher'] ?? '',
                        $r['titular'],
                        $r['dni'],
                        $r['sede'],
                        $r['cancha'],
                        $r['deporte'],
                        $r['estado'],
                        $r['pago'],
                        $r['referencia'],
                    ]));

                    return str_contains($haystack, $q);
                });
            })
            ->values();
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        $reservas = $this->reservasFiltradas();
        $porFecha = $reservas->groupBy('fecha');

        $inicioMes = Carbon::create($this->anio, $this->mes, 1)->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth();
        $cursor = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrilla = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        $semanas = [];
        while ($cursor->lte($finGrilla)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->toDateString();
                $delMes = $cursor->month === $this->mes;
                $semana[] = [
                    'fecha' => $key,
                    'dia' => $cursor->day,
                    'del_mes' => $delMes,
                    'es_hoy' => $cursor->isToday(),
                    'reservas' => $delMes ? ($porFecha->get($key, collect())->values()->all()) : [],
                ];
                $cursor->addDay();
            }
            $semanas[] = $semana;
        }

        $tituloMes = $inicioMes->locale('es')->translatedFormat('F Y');

        $detalleDia = collect();
        if ($this->fechaSeleccionada) {
            $detalleDia = $porFecha->get($this->fechaSeleccionada, collect())->values();
        }

        $reservasMes = $reservas
            ->filter(function ($r) {
                $f = Carbon::parse($r['fecha']);

                return (int) $f->month === $this->mes && (int) $f->year === $this->anio;
            })
            ->sortBy(['fecha', 'horario'])
            ->values();

        return view('livewire.admin.ver-reservas', [
            'semanas' => $semanas,
            'tituloMes' => ucfirst($tituloMes),
            'detalleDia' => $detalleDia,
            'reservasMes' => $reservasMes,
            'totalMes' => $reservasMes->count(),
        ]);
    }
}
