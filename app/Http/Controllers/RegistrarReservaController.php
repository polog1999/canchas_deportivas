<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Cancha;
use App\Models\Pago;
use App\Models\Perfil;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\Transaccion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarReservaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'acepto_terminos' => 'accepted',
            'cancha_id' => 'required|integer|exists:canchas,id',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|string',
            'duracion' => 'required|integer|in:60,120',
            'precio' => 'nullable|numeric|min:0',
            'documento' => 'nullable|string|min:8|max:15',
            'nombres' => 'nullable|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'distrito_id' => 'nullable|string|max:20',
            'estado_titular' => 'nullable|in:existe,nuevo',
            'sede' => 'nullable',
            'club' => 'nullable|string|max:255',
            'cancha' => 'nullable|string|max:255',
            'deporte' => 'nullable|string|max:255',
            'deporte_id' => 'nullable',
        ], [
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones.',
            'cancha_id.required' => 'Falta la cancha de la reserva.',
            'fecha.required' => 'Falta la fecha de la reserva.',
            'hora.required' => 'Falta la hora de la reserva.',
        ]);

        $usuario = $this->resolverUsuario($data);

        $hora = preg_replace('/[^\d:]/', '', (string) $data['hora']);
        if (! preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            throw ValidationException::withMessages(['hora' => 'Hora inválida.']);
        }

        $horaInicio = Carbon::createFromFormat('Y-m-d H:i', $data['fecha'] . ' ' . $hora);
        $horaFin = $horaInicio->copy()->addMinutes((int) $data['duracion']);
        $precio = (float) ($data['precio'] ?? 0);

        $cancha = Cancha::query()->whereKey($data['cancha_id'])->where('esta_activo', true)->first();
        if (! $cancha) {
            throw ValidationException::withMessages(['cancha_id' => 'La cancha no está disponible.']);
        }

        // Evitar solape básico con otras reservas
        $solapa = Reserva::query()
            ->where('cancha_id', $cancha->id)
            ->where(function ($q) {
                $q->whereNull('estado')
                    ->orWhereRaw('UPPER(estado) <> ?', ['CANCELADA']);
            })
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->exists();

        if ($solapa) {
            throw ValidationException::withMessages([
                'hora' => 'Ese horario ya no está disponible. Elige otro turno.',
            ]);
        }

        $resultado = DB::transaction(function () use ($data, $usuario, $cancha, $horaInicio, $horaFin, $precio) {
            $codigoVoucher = 'VCH-' . strtoupper(Str::random(8));
            $transaccionLocal = 'LOCAL-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

            $reserva = Reserva::create([
                'usuario_id' => $usuario->id,
                'cancha_id' => $cancha->id,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'precio_total' => $precio,
                'referencia_pago' => $codigoVoucher,
                'estado' => 'confirmada',
            ]);

            $transaccion = Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => $transaccionLocal,
                'codigo_autorizacion' => null,
                'marca_tarjeta' => null,
                'tarjeta_enmascarada' => null,
                'monto' => $precio,
                'estado' => 'SIN_PASARELA',
                'respuesta_bruta' => [
                    'origen' => 'maqueta',
                    'sin_niubiz' => true,
                    'voucher' => $codigoVoucher,
                    'titular' => [
                        'documento' => $data['documento'] ?? null,
                        'nombres' => $data['nombres'] ?? null,
                        'apellido_paterno' => $data['apellido_paterno'] ?? null,
                        'apellido_materno' => $data['apellido_materno'] ?? null,
                        'telefono' => $data['telefono'] ?? null,
                        'email' => $data['email'] ?? $usuario->correo_electronico,
                        'distrito_id' => $data['distrito_id'] ?? null,
                    ],
                    'reserva' => [
                        'sede_id' => $data['sede'] ?? $cancha->sede_id,
                        'club' => $data['club'] ?? null,
                        'cancha' => $data['cancha'] ?? $cancha->nombre,
                        'deporte' => $data['deporte'] ?? null,
                        'deporte_id' => $data['deporte_id'] ?? null,
                    ],
                ],
            ]);

            $pago = Pago::create([
                'transaccion_id' => $transaccion->id,
                'monto' => $precio,
                'pagado_en' => now(),
                'acepto_terminos' => true,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'pago_id' => $pago->id,
                'voucher' => $codigoVoucher,
            ];
        });

        if (! Auth::check()) {
            Auth::login($usuario);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reserva registrada correctamente.',
            'reserva_id' => $resultado['reserva_id'],
            'voucher' => $resultado['voucher'],
            'redirect' => url('/?reserva=' . $resultado['reserva_id']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolverUsuario(array $data): Usuario
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $documento = preg_replace('/\D+/', '', (string) ($data['documento'] ?? ''));

        if (strlen($documento) >= 8) {
            $perfil = Perfil::query()
                ->with('usuario')
                ->where('numero_documento', $documento)
                ->whereHas('usuario', fn ($q) => $q->where('activo', true))
                ->first();

            if ($perfil?->usuario) {
                return $perfil->usuario;
            }
        }

        $nombres = trim((string) ($data['nombres'] ?? ''));
        $apellidoPaterno = trim((string) ($data['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string) ($data['apellido_materno'] ?? ''));

        if ($nombres === '' || $apellidoPaterno === '' || $apellidoMaterno === '' || strlen($documento) < 8) {
            throw ValidationException::withMessages([
                'documento' => 'Faltan datos del titular. Vuelve a confirmar la reserva.',
            ]);
        }

        $rolCliente = Rol::firstOrCreate(
            ['nombre' => 'cliente'],
            ['descripcion' => 'Cliente', 'activo' => true]
        );

        $usuarioLogin = $documento;
        if (Usuario::where('usuario', $usuarioLogin)->exists()) {
            $usuarioLogin = 'u' . $documento . Str::lower(Str::random(3));
        }

        $usuario = Usuario::create([
            'rol_id' => $rolCliente->id,
            'usuario' => $usuarioLogin,
            'correo_electronico' => $data['email'] ?? null,
            'clave' => $documento, // cast hashed en Usuario
            'activo' => true,
        ]);

        $usuario->perfil()->create([
            'tipo_documento' => DocumentType::DNI->value,
            'numero_documento' => $documento,
            'nombres' => $nombres,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'ubigeo_distrito' => $data['distrito_id'] ?? null,
        ]);

        return $usuario->fresh('perfil');
    }
}
