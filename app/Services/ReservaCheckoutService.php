<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Transaccion;
use App\Models\Usuario;
use App\Support\CatalogoTusneReserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservaCheckoutService
{
    public const TTL_SECONDS = 1200;

    private const CHECKOUT_PREFIX = 'reserva_checkout:';

    private const SLOT_PREFIX = 'reserva_slot:';

    public function __construct(
        private readonly ReservaTitularService $titularService,
    ) {}

    public function generarPurchaseNumber(): string
    {
        do {
            // Niubiz acepta como máximo 12 dígitos numéricos.
            $number = substr((string) now()->timestamp, -9)
                .str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        } while (Cache::has(self::CHECKOUT_PREFIX.$number));

        return $number;
    }

    public function claveBloqueo(int $canchaId, Carbon $hora): string
    {
        return self::SLOT_PREFIX.$canchaId.':'.$hora->format('Y-m-d H').':00:00';
    }

    /**
     * Una clave por cada hora que abarca el turno.
     *
     * @return list<string>
     */
    private function clavesBloqueo(int $canchaId, Carbon $horaInicio, Carbon $horaFin): array
    {
        $claves = [];
        $cursor = $horaInicio->copy()->startOfHour();

        while ($cursor->lt($horaFin)) {
            $claves[] = $this->claveBloqueo($canchaId, $cursor);
            $cursor->addHour();
        }

        return $claves;
    }

    /**
     * Horas retenidas por checkouts en curso (aún sin pago).
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $canchaIds
     * @return array<int, list<int>>
     */
    public function horasRetenidasPorCancha($canchaIds, string $fecha): array
    {
        $retenidas = [];
        $dia = Carbon::parse($fecha)->startOfDay();

        foreach (collect($canchaIds)->filter()->unique() as $canchaId) {
            $canchaId = (int) $canchaId;
            $horas = [];

            for ($h = 0; $h < 24; $h++) {
                $clave = $this->claveBloqueo($canchaId, $dia->copy()->setTime($h, 0));

                if (Cache::has($clave)) {
                    $horas[] = $h;
                }
            }

            $retenidas[$canchaId] = $horas;
        }

        return $retenidas;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{
     *     usuario: Usuario|null,
     *     es_nuevo: bool,
     *     datos_registro: array<string, mixed>|null,
     *     usuario_login: string|null,
     *     clave_plana: string|null
     * }  $titular
     * @return array<string, mixed>
     */
    public function crear(
        array $data,
        array $titular,
        Carbon $horaInicio,
        Carbon $horaFin,
        float $precio,
        int $canchaId,
        ?string $returnQuery = null,
    ): array {
        if (! $this->turnoDisponible($canchaId, $horaInicio, $horaFin)) {
            throw ValidationException::withMessages([
                'hora' => 'Ese horario ya no está disponible. Elige otro turno.',
            ]);
        }

        $purchaseNumber = $this->generarPurchaseNumber();
        $voucher = 'VCH-'.strtoupper(Str::random(8));
        $catalogoTusneId = isset($data['tusne_id']) ? (int) $data['tusne_id'] : null;

        $checkout = [
            'purchase_number' => $purchaseNumber,
            'voucher' => $voucher,
            'cancha_id' => $canchaId,
            'hora_inicio' => $horaInicio->toIso8601String(),
            'hora_fin' => $horaFin->toIso8601String(),
            'precio' => round($precio, 2),
            'duracion' => (int) $data['duracion'],
            'cantidad_horas' => (int) ((int) $data['duracion'] / 60),
            'acepto_terminos' => true,
            'usuario_id' => $titular['usuario']?->id,
            'es_usuario_nuevo' => $titular['es_nuevo'],
            'datos_registro' => $titular['datos_registro'],
            'usuario_login' => $titular['usuario_login'],
            'clave_plana' => $titular['clave_plana'],
            'meta' => [
                'documento' => $data['documento'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? $titular['usuario']?->correo_electronico,
                'sede' => $data['sede'] ?? null,
                'club' => $data['club'] ?? null,
                'cancha' => $data['cancha'] ?? null,
                'deporte' => $data['deporte'] ?? null,
                'deporte_id' => $data['deporte_id'] ?? null,
                'tusne_id' => $catalogoTusneId,
            ],
            'return_query' => $returnQuery,
        ];

        Cache::put(self::CHECKOUT_PREFIX.$purchaseNumber, $checkout, self::TTL_SECONDS);

        foreach ($this->clavesBloqueo($canchaId, $horaInicio, $horaFin) as $clave) {
            Cache::put($clave, $purchaseNumber, self::TTL_SECONDS);
        }

        return $checkout;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function obtener(string $purchaseNumber): ?array
    {
        $checkout = Cache::get(self::CHECKOUT_PREFIX.$purchaseNumber);

        return is_array($checkout) ? $checkout : null;
    }

    public function liberar(string $purchaseNumber): void
    {
        $checkout = $this->obtener($purchaseNumber);

        if (! $checkout) {
            return;
        }

        Cache::forget(self::CHECKOUT_PREFIX.$purchaseNumber);

        $claves = $this->clavesBloqueo(
            (int) $checkout['cancha_id'],
            Carbon::parse($checkout['hora_inicio']),
            Carbon::parse($checkout['hora_fin']),
        );

        foreach ($claves as $clave) {
            Cache::forget($clave);
        }
    }

    public function turnoDisponible(
        int $canchaId,
        Carbon $horaInicio,
        Carbon $horaFin,
        ?string $purchaseNumberPropio = null,
    ): bool {
        $ocupada = Reserva::query()
            ->where('cancha_id', $canchaId)
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->where(function ($q) {
                $q->whereRaw('LOWER(estado) = ?', ['confirmada'])
                    ->orWhereRaw('LOWER(estado) = ?', ['pendiente']);
            })
            ->exists();

        if ($ocupada) {
            return false;
        }

        foreach ($this->clavesBloqueo($canchaId, $horaInicio, $horaFin) as $clave) {
            $bloqueo = Cache::get($clave);

            if ($bloqueo === null) {
                continue;
            }

            if ($purchaseNumberPropio === null || (string) $bloqueo !== $purchaseNumberPropio) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $checkout
     * @param  array<string, mixed>  $niubiz
     * @return array{
     *     reserva: Reserva,
     *     pago: Pago,
     *     usuario: Usuario,
     *     es_usuario_nuevo: bool,
     *     usuario_login: string|null,
     *     clave_plana: string|null,
     *     meta: array<string, mixed>
     * }
     */
    public function materializar(
        array $checkout,
        array $niubiz,
    ): array {
        $horaInicio = Carbon::parse($checkout['hora_inicio']);
        $horaFin = Carbon::parse($checkout['hora_fin']);
        $purchaseNumber = (string) $checkout['purchase_number'];

        if (! $this->turnoDisponible(
            (int) $checkout['cancha_id'],
            $horaInicio,
            $horaFin,
            $purchaseNumber,
        )) {
            throw ValidationException::withMessages([
                'hora' => 'Ese horario ya no está disponible.',
            ]);
        }

        $meta = is_array($checkout['meta'] ?? null) ? $checkout['meta'] : [];

        $resultado = DB::transaction(function () use ($checkout, $niubiz, $horaInicio, $horaFin, $meta) {
            $usuario = null;

            if (! empty($checkout['usuario_id'])) {
                $usuario = Usuario::query()->with('perfil')->find($checkout['usuario_id']);
            }

            if (! $usuario && is_array($checkout['datos_registro'] ?? null)) {
                $usuario = $this->titularService->crearUsuario($checkout['datos_registro']);
            }

            if (! $usuario) {
                throw ValidationException::withMessages([
                    'documento' => 'No se pudo identificar al titular de la reserva.',
                ]);
            }

            $reserva = Reserva::create([
                'usuario_id' => $usuario->id,
                'cancha_id' => (int) $checkout['cancha_id'],
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'precio_total' => round((float) $checkout['precio'], 2),
                'referencia_pago' => (string) $checkout['voucher'],
                'estado' => 'confirmada',
                'cantidad_horas' => (int) ($checkout['cantidad_horas'] ?? 1),
            ]);

            $transaccion = Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => (string) $niubiz['transaction_id'],
                'codigo_autorizacion' => $niubiz['auth_code'] ? (string) $niubiz['auth_code'] : null,
                'marca_tarjeta' => $niubiz['brand'] ? (string) $niubiz['brand'] : null,
                'tarjeta_enmascarada' => $niubiz['card'] ? (string) $niubiz['card'] : null,
                'monto' => round((float) $niubiz['amount'], 2),
                'estado' => 'Authorized',
                'respuesta_bruta' => [
                    'niubiz' => $niubiz['response'],
                    'voucher' => $reserva->referencia_pago,
                    'meta' => $meta,
                    'checkout' => $checkout['purchase_number'],
                ],
            ]);

            $pago = Pago::create([
                'transaccion_id' => $transaccion->id,
                'monto' => round((float) $niubiz['amount'], 2),
                'pagado_en' => now('UTC'),
                'acepto_terminos' => (bool) ($checkout['acepto_terminos'] ?? true),
                'id_catalogos_tusne' => CatalogoTusneReserva::idDesdeMeta($meta),
            ]);

            return [
                'reserva' => $reserva,
                'pago' => $pago,
                'usuario' => $usuario->fresh('perfil'),
            ];
        });

        $this->liberar($purchaseNumber);

        return [
            'reserva' => $resultado['reserva'],
            'pago' => $resultado['pago'],
            'usuario' => $resultado['usuario'],
            'es_usuario_nuevo' => (bool) ($checkout['es_usuario_nuevo'] ?? false),
            'usuario_login' => $checkout['usuario_login'] ?? null,
            'clave_plana' => $checkout['clave_plana'] ?? null,
            'meta' => $meta,
        ];
    }

    /**
     * @return array{email: string, usuario_id: string, dias_registro: int, es_registrado: bool}
     */
    public function datosAntifraude(array $checkout, ?Usuario $usuario): array
    {
        $email = trim((string) ($checkout['meta']['email'] ?? $usuario?->correo_electronico ?? ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'reservas@munilamolina.gob.pe';
        }

        $diasRegistro = 1;

        if ($usuario?->creado_en) {
            $diasRegistro = max(1, (int) $usuario->creado_en->diffInDays(now()));
        }

        return [
            'email' => $email,
            'usuario_id' => (string) ($usuario?->id ?? '0'),
            'dias_registro' => $diasRegistro,
            'es_registrado' => $usuario !== null,
        ];
    }
}
