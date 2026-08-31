<?php

namespace App\Services;

use App\Mail\CodigoVerificacionContrasenaMail;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CambioContrasenaService
{
    private const TTL_CODIGO = 1800; // 30 min

    private const TTL_VERIFICADO = 1800; // 30 min

    private const MIN_REENVIO = 30; // 30 seg

    public function enmascararCorreo(string $correo): string
    {
        $correo = trim($correo);
        if (! str_contains($correo, '@')) {
            return $correo;
        }

        [$local, $dominio] = explode('@', $correo, 2);
        $visible = mb_substr($local, 0, 1);
        $oculto = max(1, mb_strlen($local) - 1);

        return $visible.str_repeat('*', $oculto).'@'.$dominio;
    }

    public function enviarCodigo(Usuario $usuario): array
    {
        $correo = trim((string) $usuario->correo_electronico);
        if ($correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'mensaje' => 'Tu cuenta no tiene un correo válido registrado. Contacta al administrador.',
            ];
        }

        $ultimoEnvio = Cache::get($this->claveReenvio($usuario->id));
        if ($ultimoEnvio && (time() - (int) $ultimoEnvio) < self::MIN_REENVIO) {
            $espera = self::MIN_REENVIO - (time() - (int) $ultimoEnvio);

            return [
                'ok' => false,
                'mensaje' => "Espera {$espera} segundos antes de solicitar otro código.",
            ];
        }

        $codigo = (string) random_int(100000, 999999);

        Cache::put($this->claveCodigo($usuario->id), Hash::make($codigo), self::TTL_CODIGO);
        Cache::put($this->claveIntentos($usuario->id), 0, self::TTL_CODIGO);
        Cache::put($this->claveReenvio($usuario->id), time(), self::MIN_REENVIO);
        Cache::forget($this->claveVerificado($usuario->id));

        $mailer = app(MailConfigService::class)->mailerActivo();

        try {
            Mail::mailer($mailer)->to($correo)->send(new CodigoVerificacionContrasenaMail(
                usuario: $usuario,
                codigo: $codigo,
            ));

            Log::info('CambioContrasena: código enviado', [
                'usuario_id' => $usuario->id,
                'destino' => $this->enmascararCorreo($correo),
                'mailer' => $mailer,
            ]);

            return [
                'ok' => true,
                'mensaje' => 'Te enviamos un código de verificación a tu correo.',
            ];
        } catch (\Throwable $e) {
            Cache::forget($this->claveCodigo($usuario->id));
            Cache::forget($this->claveIntentos($usuario->id));

            Log::error('CambioContrasena: error al enviar código', [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'mensaje' => 'No se pudo enviar el correo. Intenta nuevamente en unos minutos.',
            ];
        }
    }

    public function verificarCodigo(Usuario $usuario, string $codigo): array
    {
        $codigo = trim($codigo);
        if (! preg_match('/^\d{6}$/', $codigo)) {
            return [
                'ok' => false,
                'mensaje' => 'Ingresa un código válido de 6 dígitos.',
            ];
        }

        $hash = Cache::get($this->claveCodigo($usuario->id));
        if (! is_string($hash) || $hash === '') {
            return [
                'ok' => false,
                'mensaje' => 'El código expiró. Solicita uno nuevo.',
            ];
        }

        $intentos = (int) Cache::get($this->claveIntentos($usuario->id), 0);
        if ($intentos >= 5) {
            Cache::forget($this->claveCodigo($usuario->id));

            return [
                'ok' => false,
                'mensaje' => 'Demasiados intentos fallidos. Solicita un nuevo código.',
            ];
        }

        if (! Hash::check($codigo, $hash)) {
            Cache::put($this->claveIntentos($usuario->id), $intentos + 1, self::TTL_CODIGO);

            return [
                'ok' => false,
                'mensaje' => 'Código incorrecto. Revisa tu correo e intenta de nuevo.',
            ];
        }

        Cache::forget($this->claveCodigo($usuario->id));
        Cache::forget($this->claveIntentos($usuario->id));
        Cache::put($this->claveVerificado($usuario->id), true, self::TTL_VERIFICADO);

        return [
            'ok' => true,
            'mensaje' => 'Correo verificado. Ya puedes definir tu nueva contraseña.',
        ];
    }

    public function estaVerificado(int $usuarioId): bool
    {
        return (bool) Cache::get($this->claveVerificado($usuarioId), false);
    }

    public function cambiarContrasena(Usuario $usuario, string $password): array
    {
        if (! $this->estaVerificado((int) $usuario->id)) {
            return [
                'ok' => false,
                'mensaje' => 'Debes verificar tu correo antes de cambiar la contraseña.',
            ];
        }

        $usuario->forceFill([
            'clave' => $password,
        ])->save();

        $this->limpiarVerificacion((int) $usuario->id);

        Log::info('CambioContrasena: contraseña actualizada', [
            'usuario_id' => $usuario->id,
        ]);

        return [
            'ok' => true,
            'mensaje' => 'Tu contraseña fue actualizada correctamente.',
        ];
    }

    public function limpiarVerificacion(int $usuarioId): void
    {
        Cache::forget($this->claveVerificado($usuarioId));
        Cache::forget($this->claveCodigo($usuarioId));
        Cache::forget($this->claveIntentos($usuarioId));
    }

    private function claveCodigo(int $usuarioId): string
    {
        return 'cambio_clave_codigo_'.$usuarioId;
    }

    private function claveIntentos(int $usuarioId): string
    {
        return 'cambio_clave_intentos_'.$usuarioId;
    }

    private function claveVerificado(int $usuarioId): string
    {
        return 'cambio_clave_verificado_'.$usuarioId;
    }

    private function claveReenvio(int $usuarioId): string
    {
        return 'cambio_clave_reenvio_'.$usuarioId;
    }
}
