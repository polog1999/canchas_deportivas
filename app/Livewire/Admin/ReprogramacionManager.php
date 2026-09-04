<?php

namespace App\Livewire\Admin;

use App\Models\Reserva;
use App\Models\Usuario;
use App\Services\ReprogramacionReservaService;
use App\Services\ReservaCorreoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ReprogramacionManager extends Component
{
    public string $search = '';

    public ?int $reservaId = null;

    public string $fecha = '';

    public string $motivo = '';

    public ?int $slotCanchaId = null;

    public ?int $slotHora = null;

    public function mount(?int $reserva = null): void
    {
        $this->fecha = Carbon::now('America/Lima')->toDateString();

        if ($reserva) {
            $this->seleccionarReserva($reserva);
        }
    }

    public function seleccionarReserva(int $id): void
    {
        $this->reservaId = $id;
        $this->cerrarConfirmacion();

        $reserva = $this->reserva();

        // Arranca en la fecha del turno vigente, o en hoy si esa fecha ya pasó.
        if ($reserva) {
            $hoy = Carbon::now('America/Lima')->startOfDay();
            $vigente = $reserva->horaInicioVigente()->startOfDay();
            $this->fecha = $vigente->lt($hoy) ? $hoy->toDateString() : $vigente->toDateString();
        }
    }

    public function limpiarSeleccion(): void
    {
        $this->reservaId = null;
        $this->cerrarConfirmacion();
    }

    public function diaAnterior(): void
    {
        $this->fecha = Carbon::parse($this->fecha)->subDay()->toDateString();
        $this->cerrarConfirmacion();
    }

    public function diaSiguiente(): void
    {
        $this->fecha = Carbon::parse($this->fecha)->addDay()->toDateString();
        $this->cerrarConfirmacion();
    }

    public function updatedFecha(): void
    {
        $this->cerrarConfirmacion();
    }

    public function elegirSlot(int $canchaId, int $hora): void
    {
        $this->slotCanchaId = $canchaId;
        $this->slotHora = $hora;
        $this->motivo = '';
        $this->resetErrorBag();
    }

    public function cerrarConfirmacion(): void
    {
        $this->slotCanchaId = null;
        $this->slotHora = null;
        $this->motivo = '';
        $this->resetErrorBag();
    }

    public function confirmar(ReprogramacionReservaService $servicio, ReservaCorreoService $correo): void
    {
        $reserva = $this->reserva();

        if (! $reserva || $this->slotCanchaId === null || $this->slotHora === null) {
            return;
        }

        /** @var Usuario $autorizador */
        $autorizador = Auth::user();

        $horaInicio = Carbon::createFromFormat(
            'Y-m-d H:i',
            $this->fecha.' '.sprintf('%02d:00', $this->slotHora),
            'America/Lima',
        );

        try {
            $reprogramacion = $servicio->reprogramar(
                $reserva,
                $this->slotCanchaId,
                $horaInicio,
                $autorizador,
                trim($this->motivo) !== '' ? trim($this->motivo) : null,
            );
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        // El correo no debe tumbar la reprogramación si el SMTP falla.
        $correo->enviarReprogramacion($reprogramacion);

        $this->cerrarConfirmacion();
        $this->fecha = $horaInicio->toDateString();

        session()->flash(
            'reprogramacion_ok',
            'Reserva reprogramada al '.$horaInicio->locale('es')->translatedFormat('D d/m/Y').
            ' a las '.$horaInicio->format('H:i').'. El turno anterior quedó libre '
            .'y se envió la nueva constancia al cliente.',
        );
    }

    private function reserva(): ?Reserva
    {
        if (! $this->reservaId) {
            return null;
        }

        return Reserva::query()
            ->with([
                'usuario.perfil',
                'cancha.sede',
                'transacciones',
                'reprogramacionVigente.canchaNueva',
            ])
            ->find($this->reservaId);
    }

    #[Layout('components.app-layout')]
    public function render(ReprogramacionReservaService $servicio)
    {
        $termino = mb_strtolower(trim($this->search));

        $reservas = Reserva::query()
            ->with(['usuario.perfil', 'cancha', 'reprogramacionVigente.canchaNueva'])
            ->whereRaw('LOWER(estado) = ?', ['confirmada'])
            ->when($termino !== '', function ($q) use ($termino) {
                $like = '%'.$termino.'%';

                $q->where(function ($sub) use ($like, $termino) {
                    $sub->whereRaw('LOWER(referencia_pago) LIKE ?', [$like])
                        ->orWhere('id', (int) filter_var($termino, FILTER_SANITIZE_NUMBER_INT) ?: 0)
                        ->orWhereHas('usuario.perfil', fn ($p) => $p->whereRaw('LOWER(numero_documento) LIKE ?', [$like]))
                        ->orWhereHas('usuario', fn ($u) => $u->whereRaw('LOWER(correo_electronico) LIKE ?', [$like]));
                });
            })
            ->orderByDesc('hora_inicio')
            ->limit(40)
            ->get();

        $reserva = $this->reserva();
        $contexto = null;
        $grilla = null;
        $historial = collect();

        if ($reserva) {
            $contexto = $servicio->contexto($reserva);
            $grilla = $servicio->grilla($reserva, $this->fecha);
            $historial = $reserva->reprogramaciones()
                ->with(['canchaAnterior', 'canchaNueva', 'autorizadoPor'])
                ->orderByDesc('id')
                ->get();
        }

        return view('livewire.admin.reprogramacion', [
            'reservas' => $reservas,
            'reserva' => $reserva,
            'contexto' => $contexto,
            'grilla' => $grilla,
            'historial' => $historial,
            'fechaLabel' => Carbon::parse($this->fecha)->locale('es')->translatedFormat('D d/m/Y'),
        ]);
    }
}
