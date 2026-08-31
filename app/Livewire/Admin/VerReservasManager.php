<?php

namespace App\Livewire\Admin;

use App\Models\Reserva;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        $hoy = Carbon::now('America/Lima');
        $this->mes = (int) $hoy->month;
        $this->anio = (int) $hoy->year;
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
        $hoy = Carbon::now('America/Lima');
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

        $fecha = Carbon::parse($match['fecha']);
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
        $this->fechaSeleccionada = $fecha->toDateString();
    }

    public function seleccionarDia(string $fecha): void
    {
        $this->fechaSeleccionada = $fecha;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function reservasReales(): Collection
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            return collect();
        }

        $esAdmin = $usuario->tieneRol('admin', 'ADMIN', 'SUPERADMIN');

        return Reserva::query()
            ->with([
                'usuario.perfil',
                'cancha.sede',
                'cancha.deportes',
                'transacciones' => fn ($q) => $q->orderByDesc('id'),
            ])
            ->when(! $esAdmin, fn ($q) => $q->where('usuario_id', $usuario->id))
            ->orderByDesc('hora_inicio')
            ->get()
            ->map(fn (Reserva $reserva) => $this->mapearReserva($reserva))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapearReserva(Reserva $reserva): array
    {
        $titular = $reserva->usuario;
        $perfil = $titular?->perfil;
        $cancha = $reserva->cancha;
        $sede = $cancha?->sede;
        $transaccion = $reserva->transacciones->first();

        $horaInicio = $reserva->hora_inicio;
        $horaFin = $reserva->hora_fin;

        $fecha = $horaInicio
            ? $horaInicio->format('Y-m-d')
            : ($reserva->creado_en?->format('Y-m-d') ?? now()->format('Y-m-d'));

        $fechaCarbon = Carbon::parse($fecha.' 12:00:00', 'America/Lima');

        $duracionMin = ($horaInicio && $horaFin)
            ? (int) $horaInicio->diffInMinutes($horaFin)
            : 60;

        $deporte = data_get($transaccion?->respuesta_bruta, 'meta.deporte')
            ?? data_get($transaccion?->respuesta_bruta, 'reserva.deporte')
            ?? $cancha?->deportes?->first()?->nombre
            ?? '—';

        $estadoRaw = strtolower(trim((string) $reserva->estado));
        $estado = match ($estadoRaw) {
            'confirmada' => 'Confirmada',
            'pendiente' => 'Pendiente',
            'cancelada', 'pago_fallido' => 'Cancelada',
            default => ucfirst($estadoRaw ?: 'Pendiente'),
        };

        $precio = round((float) $reserva->precio_total, 2);
        $pago = match ($estadoRaw) {
            'confirmada' => $precio <= 0 || strtolower((string) $transaccion?->estado) === 'sin_pasarela'
                ? 'Gratuito'
                : 'Pagado',
            'pendiente' => 'Por pagar',
            'pago_fallido' => 'Pago fallido',
            'cancelada' => 'Cancelada',
            default => '—',
        };

        $imagen = null;
        if ($sede && method_exists($sede, 'urlImagen')) {
            $imagen = $sede->urlImagen();
        }

        return [
            'id' => $reserva->id,
            'codigo' => 'RES-'.str_pad((string) $reserva->id, 4, '0', STR_PAD_LEFT),
            'titular' => $titular?->nombreCompleto() ?? '—',
            'dni' => $perfil?->numero_documento ?? '—',
            'sede' => $sede?->nombre ?? '—',
            'direccion' => $sede?->direccion ?? '—',
            'cancha' => $cancha?->nombre ?? '—',
            'deporte' => $deporte,
            'fecha' => $fecha,
            'fecha_label' => $fechaCarbon->locale('es')->translatedFormat('D d/m/Y'),
            'horario' => ($horaInicio && $horaFin)
                ? $horaInicio->format('H:i').' — '.$horaFin->format('H:i')
                : '—',
            'duracion' => $duracionMin.' min',
            'precio' => $precio,
            'estado' => $estado,
            'pago' => $pago,
            'referencia' => $transaccion?->transaccion_id ?? '—',
            'codigo_voucher' => $reserva->referencia_pago,
            'imagen' => $imagen ?? asset('favicon.png'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function reservasFiltradas(): Collection
    {
        $q = mb_strtolower(trim($this->search));
        $estado = $this->filtroEstado;

        return $this->reservasReales()
            ->when($estado !== '', fn ($c) => $c->where('estado', $estado))
            ->when($q !== '', function (Collection $coleccion) use ($q) {
                return $coleccion->filter(function (array $r) use ($q) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
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
                    ])));

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
