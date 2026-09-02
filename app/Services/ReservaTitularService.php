<?php

namespace App\Services;

use App\Models\Perfil;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservaTitularService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     usuario: Usuario|null,
     *     es_nuevo: bool,
     *     datos_registro: array<string, mixed>|null,
     *     usuario_login: string|null,
     *     clave_plana: string|null
     * }
     */
    public function prepararParaCheckout(array $data): array
    {
        if (Auth::check()) {
            /** @var Usuario $authUser */
            $authUser = Auth::user();

            return [
                'usuario' => $authUser,
                'es_nuevo' => false,
                'datos_registro' => null,
                'usuario_login' => $authUser->correo_electronico ?: $authUser->usuario,
                'clave_plana' => null,
            ];
        }

        $documento = preg_replace('/\D+/', '', (string) ($data['documento'] ?? ''));

        if (strlen($documento) >= 8) {
            $perfil = Perfil::query()
                ->with('usuario')
                ->where('numero_documento', $documento)
                ->whereHas('usuario', fn ($q) => $q->where('activo', true))
                ->first();

            if ($perfil?->usuario) {
                return [
                    'usuario' => $perfil->usuario,
                    'es_nuevo' => false,
                    'datos_registro' => null,
                    'usuario_login' => $perfil->usuario->correo_electronico ?: $perfil->usuario->usuario,
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

        $email = trim((string) ($data['email'] ?? ''));

        return [
            'usuario' => null,
            'es_nuevo' => true,
            'datos_registro' => [
                'documento' => $documento,
                'nombres' => $nombres,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
                'telefono' => $this->normalizarTelefono($data['telefono'] ?? null),
                'tipo_documento_id' => (int) ($data['tipo_documento_id'] ?? 1),
                'email' => $email !== '' ? $email : null,
                'distrito_id' => $data['distrito_id'] ?? null,
            ],
            'usuario_login' => $email !== '' ? $email : $documento,
            'clave_plana' => $documento,
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearUsuario(array $datos): Usuario
    {
        $documento = preg_replace('/\D+/', '', (string) ($datos['documento'] ?? ''));

        $rolCliente = Rol::firstOrCreate(
            ['nombre' => 'cliente'],
            ['descripcion' => 'Cliente', 'activo' => true]
        );

        $usuarioLogin = $documento;

        if (Usuario::where('usuario', $usuarioLogin)->exists()) {
            $usuarioLogin = 'u'.$documento.Str::lower(Str::random(3));
        }

        $usuario = Usuario::create([
            'rol_id' => $rolCliente->id,
            'usuario' => $usuarioLogin,
            'correo_electronico' => $datos['email'] ?? null,
            'clave' => $documento,
            'activo' => true,
        ]);

        $usuario->perfil()->create([
            'tipo_documento_id' => (int) ($datos['tipo_documento_id'] ?? 1),
            'numero_documento' => $documento,
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'ubigeo_distrito' => $datos['distrito_id'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ]);

        return $usuario->fresh('perfil');
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
