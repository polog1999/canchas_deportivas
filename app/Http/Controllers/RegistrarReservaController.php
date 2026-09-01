<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Distrito;
use App\Models\Pago;
use App\Models\Perfil;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\Transaccion;
use App\Models\Usuario;
use App\Services\NiubizService;
use App\Services\OracleService;
use App\Services\ReservaCorreoService;
use App\Support\CatalogoTusneReserva;
use App\Support\ReservaFlow;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarReservaController extends Controller
{
    public function __invoke(Request $request, NiubizService $niubiz): JsonResponse
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
            'telefono' => 'nullable|string|max:9',
            'tipo_documento_id' => 'nullable|integer|in:1,2,3',
            'email' => 'nullable|email|max:150',
            'distrito_id' => 'nullable|string|max:20',
            'estado_titular' => 'nullable|in:existe,nuevo',
            'sede' => 'nullable',
            'club' => 'nullable|string|max:255',
            'cancha' => 'nullable|string|max:255',
            'deporte' => 'nullable|string|max:255',
            'deporte_id' => 'nullable',
            'tusne_id' => 'nullable|integer|exists:catalogos_tusne,id',
            'reserva_id' => 'nullable|integer',
        ], [
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones.',
            'cancha_id.required' => 'Falta la cancha de la reserva.',
            'fecha.required' => 'Falta la fecha de la reserva.',
            'hora.required' => 'Falta la hora de la reserva.',
        ]);

        $usuarioData = $this->resolverUsuario($data);
        $usuario = $usuarioData['usuario'];

        $hora = preg_replace('/[^\d:]/', '', (string) $data['hora']);
        if (! preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            throw ValidationException::withMessages(['hora' => 'Hora inválida.']);
        }

        $horaInicio = Carbon::createFromFormat('Y-m-d H:i', $data['fecha'] . ' ' . $hora, 'America/Lima');
        $horaFin = $horaInicio->copy()->addMinutes((int) $data['duracion']);
        $precio = round((float) ($data['precio'] ?? 0), 2);

        $cancha = Cancha::query()->whereKey($data['cancha_id'])->where('esta_activo', true)->first();
        if (! $cancha) {
            throw ValidationException::withMessages(['cancha_id' => 'La cancha no está disponible.']);
        }

        $reserva = null;
        if (! empty($data['reserva_id'])) {
            $reserva = Reserva::query()
                ->whereKey($data['reserva_id'])
                ->where('usuario_id', $usuario->id)
                ->whereRaw('LOWER(estado) = ?', ['pendiente'])
                ->first();
        }

        if (! $reserva) {
            $solapa = Reserva::query()
                ->where('cancha_id', $cancha->id)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->where(function ($q) {
                    $q->whereNull('estado')
                        ->orWhereRaw('UPPER(estado) NOT IN (?, ?)', ['CANCELADA', 'PAGO_FALLIDO']);
                })
                ->where(function ($q) use ($usuario) {
                    $q->whereRaw('LOWER(estado) = ?', ['confirmada'])
                        ->orWhere(function ($q2) use ($usuario) {
                            $q2->whereRaw('LOWER(estado) = ?', ['pendiente'])
                                ->where('usuario_id', '<>', $usuario->id);
                        });
                })
                ->exists();

            if ($solapa) {
                throw ValidationException::withMessages([
                    'hora' => 'Ese horario ya no está disponible. Elige otro turno.',
                ]);
            }

            // Cancelar pendientes propias del mismo slot para no acumular
            Reserva::query()
                ->where('usuario_id', $usuario->id)
                ->where('cancha_id', $cancha->id)
                ->where('hora_inicio', $horaInicio)
                ->where('hora_fin', $horaFin)
                ->whereRaw('LOWER(estado) = ?', ['pendiente'])
                ->update(['estado' => 'cancelada']);

            $codigoVoucher = 'VCH-' . strtoupper(Str::random(8));

            $reserva = Reserva::create([
                'usuario_id' => $usuario->id,
                'cancha_id' => $cancha->id,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'precio_total' => $precio,
                'referencia_pago' => $codigoVoucher,
                'estado' => 'pendiente',
            ]);
        }

        if (! Auth::check()) {
            Auth::login($usuario);
        }

        $returnQuery = $request->header('X-Pago-Query') ?: $request->getQueryString();
        $catalogoTusneId = CatalogoTusneReserva::idDesdeDatos($data, $cancha);

        session([
            'pago_reserva_id' => $reserva->id,
            'pago_acepto_terminos' => true,
            'pago_return_query' => $returnQuery,
            'pago_usuario_nuevo' => $usuarioData['es_nuevo'],
            'pago_usuario_login' => $usuarioData['usuario_login'],
            'pago_clave_plana' => $usuarioData['clave_plana'],
            'pago_meta' => [
                'documento' => $data['documento'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? $usuario->correo_electronico,
                'sede' => $data['sede'] ?? $cancha->sede_id,
                'club' => $data['club'] ?? null,
                'cancha' => $data['cancha'] ?? $cancha->nombre,
                'deporte' => $data['deporte'] ?? null,
                'deporte_id' => $data['deporte_id'] ?? null,
                'tusne_id' => $catalogoTusneId,
            ],
        ]);

        // Monto 0: confirmar sin pasarela
        if ($precio <= 0) {
            return $this->confirmarSinPasarela($reserva, $data, $usuario, $cancha, $usuarioData);
        }

        $sessionKey = $niubiz->createSessionToken($reserva, $precio, $usuario);

        if (! $sessionKey) {
            throw ValidationException::withMessages([
                'niubiz' => 'No se pudo conectar con la pasarela de pago. Intenta nuevamente.',
            ]);
        }

        $timeoutUrl = ReservaFlow::desdePortalActivo()
            ? route('portal.reservar.pago')
            : route('reservar.pago');
        if ($returnQuery) {
            $timeoutUrl .= '?' . ltrim($returnQuery, '?');
        }

        return response()->json([
            'ok' => true,
            'sin_pasarela' => false,
            'sessionKey' => $sessionKey,
            'purchaseNumber' => (string) $reserva->id,
            'amount' => number_format($precio, 2, '.', ''),
            'merchantId' => config('niubiz.merchant_id'),
            'reserva_id' => $reserva->id,
            'voucher' => $reserva->referencia_pago,
            'verifyUrl' => route('reservar.pago.verificar', ['purchaseNumber' => $reserva->id]),
            'timeoutUrl' => $timeoutUrl,
            'buttonUrl' => config('niubiz.button_url'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{usuario: Usuario, es_nuevo: bool, usuario_login: ?string, clave_plana: ?string}  $usuarioData
     */
    private function confirmarSinPasarela(
        Reserva $reserva,
        array $data,
        Usuario $usuario,
        Cancha $cancha,
        array $usuarioData,
    ): JsonResponse {
        $resultado = DB::transaction(function () use ($reserva, $data, $usuario, $cancha) {
            $reserva->update(['estado' => 'confirmada']);

            $catalogoTusneId = CatalogoTusneReserva::idDesdeDatos($data, $cancha);

            $transaccion = Transaccion::create([
                'reserva_id' => $reserva->id,
                'transaccion_id' => 'LOCAL-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'codigo_autorizacion' => null,
                'marca_tarjeta' => null,
                'tarjeta_enmascarada' => null,
                'monto' => 0,
                'estado' => 'SIN_PASARELA',
                'respuesta_bruta' => [
                    'origen' => 'monto_cero',
                    'sin_niubiz' => true,
                    'voucher' => $reserva->referencia_pago,
                    'titular' => [
                        'documento' => $data['documento'] ?? null,
                        'email' => $data['email'] ?? $usuario->correo_electronico,
                    ],
                    'reserva' => [
                        'sede_id' => $data['sede'] ?? $cancha->sede_id,
                        'club' => $data['club'] ?? null,
                        'cancha' => $data['cancha'] ?? $cancha->nombre,
                        'deporte' => $data['deporte'] ?? null,
                    ],
                ],
            ]);

            $pago = Pago::create([
                'transaccion_id' => $transaccion->id,
                'monto' => 0,
                'pagado_en' => now('UTC'),
                'acepto_terminos' => true,
                'id_catalogos_tusne' => $catalogoTusneId,
            ]);

            return [
                'reserva_id' => $reserva->id,
                'pago_id' => $pago->id,
                'voucher' => $reserva->referencia_pago,
            ];
        });

        $reserva->refresh();

        $num_documento = $usuario->perfil->numero_documento;
        app(ReservaCorreoService::class)->enviarConfirmacionPago(
            $reserva,
            array_merge($data, [
                'club' => $data['club'] ?? null,
                'cancha' => $data['cancha'] ?? $cancha->nombre,
                'deporte' => $data['deporte'] ?? null,
                'email' => $data['email'] ?? $usuario->correo_electronico,
            ]),
            $usuarioData['es_nuevo'],
            $usuarioData['usuario_login'],
            $usuarioData['clave_plana'],
            Pago::query()->find($resultado['pago_id']),
        );

        session()->forget([
            'pago_reserva_id',
            'pago_acepto_terminos',
            'pago_meta',
            'pago_return_query',
            'pago_usuario_nuevo',
            'pago_usuario_login',
            'pago_clave_plana',
        ]);
      

        return response()->json([
            'ok' => true,
            'sin_pasarela' => true,
            'mensaje' => 'Reserva confirmada (sin costo).',
            'reserva_id' => $resultado['reserva_id'],
            'voucher' => $resultado['voucher'],
            'redirect' => ReservaFlow::rutaResultado('exitoso', [
                'reserva' => $resultado['reserva_id'],
                'voucher' => $resultado['voucher'],
            ]),
        ]);
    }
    public function generarCodigoContribuyente()
    {

        // Ejecutamos la query de Oracle directamente

        $resultado = DB::connection('oracle')->selectOne("SELECT 'S' || LPAD(ADMIN.SEQ_MACARNOM.NEXTVAL, 7, '0') MCNCONTRIB FROM DUAL");

        return $resultado->mcncontrib;
    }
    /**
     * @param  array<string, mixed>  $data
     * @return array{usuario: Usuario, es_nuevo: bool, usuario_login: ?string, clave_plana: ?string}
     */
    private function resolverUsuario(array $data): array
    {
        if (Auth::check()) {
            /** @var Usuario $authUser */
            $authUser = Auth::user();

            return [
                'usuario' => $authUser,
                'es_nuevo' => false,
                'usuario_login' => $authUser->usuario,
                'clave_plana' => null,
            ];
        }

        $documento = preg_replace('/\D+/', '', (string) ($data['documento'] ?? ''));

        if (strlen($documento) >= 8) {
            $perfil = Perfil::query()
                ->with('usuario')
                ->where('numero_documento', $documento)
                ->whereHas('usuario', fn($q) => $q->where('activo', true))
                ->first();

            if ($perfil?->usuario) {
                return [
                    'usuario' => $perfil->usuario,
                    'es_nuevo' => false,
                    'usuario_login' => $perfil->usuario->usuario,
                    'clave_plana' => null,
                ];
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
            'clave' => $documento,
            'activo' => true,
        ]);

        $usuario->perfil()->create([
            'tipo_documento_id' => (int) ($data['tipo_documento_id'] ?? 1),
            'numero_documento' => $documento,
            'nombres' => $nombres,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'ubigeo_distrito' => $data['distrito_id'] ?? null,
            'telefono' => $this->normalizarTelefono($data['telefono'] ?? null),
        ]);

        return [
            'usuario' => $usuario->fresh('perfil'),
            'es_nuevo' => true,
            'usuario_login' => trim((string) ($data['email'] ?? '')) ?: $usuarioLogin,
            'clave_plana' => $documento,
        ];
    }

    private function normalizarTelefono(?string $telefono): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $telefono);

        if ($digits === '' || strlen($digits) < 7) {
            return null;
        }

        return substr($digits, 0, 9);
    }
}
