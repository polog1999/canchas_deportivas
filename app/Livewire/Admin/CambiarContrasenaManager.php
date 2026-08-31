<?php

namespace App\Livewire\Admin;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Usuario;
use App\Services\CambioContrasenaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CambiarContrasenaManager extends Component
{
    use PasswordValidationRules;

    public string $paso = 'solicitar';

    public string $codigo = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $correoEnmascarado = null;

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public function mount(CambioContrasenaService $servicio): void
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();
        if (! $usuario) {
            return;
        }

        $correo = trim((string) $usuario->correo_electronico);
        if ($correo !== '') {
            $this->correoEnmascarado = $servicio->enmascararCorreo($correo);
        }

        if ($servicio->estaVerificado((int) $usuario->id)) {
            $this->paso = 'cambiar';
        }
    }

    public function enviarCodigo(CambioContrasenaService $servicio): void
    {
        $this->resetMensajes();

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $resultado = $servicio->enviarCodigo($usuario);
        if (! $resultado['ok']) {
            $this->mensajeError = $resultado['mensaje'];

            return;
        }

        $this->paso = 'verificar';
        $this->codigo = '';
        $this->mensajeExito = $resultado['mensaje'];
    }

    public function verificarCodigo(CambioContrasenaService $servicio): void
    {
        $this->resetMensajes();

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $resultado = $servicio->verificarCodigo($usuario, $this->codigo);
        if (! $resultado['ok']) {
            $this->mensajeError = $resultado['mensaje'];

            return;
        }

        $this->paso = 'cambiar';
        $this->password = '';
        $this->password_confirmation = '';
        $this->mensajeExito = $resultado['mensaje'];
    }

    public function actualizarContrasena(CambioContrasenaService $servicio): void
    {
        $this->resetMensajes();

        Validator::make(
            [
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            [
                'password' => $this->passwordRules(),
            ],
            [
                'password.required' => 'Ingresa la nueva contraseña.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ],
        )->validate();

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $resultado = $servicio->cambiarContrasena($usuario, $this->password);
        if (! $resultado['ok']) {
            $this->mensajeError = $resultado['mensaje'];
            if (str_contains($resultado['mensaje'], 'verificar')) {
                $this->paso = 'solicitar';
            }

            return;
        }

        $this->paso = 'listo';
        $this->password = '';
        $this->password_confirmation = '';
        $this->codigo = '';
        $this->mensajeExito = $resultado['mensaje'];
    }

    public function reiniciar(CambioContrasenaService $servicio): void
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $servicio->limpiarVerificacion((int) $usuario->id);

        $this->paso = 'solicitar';
        $this->codigo = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetMensajes();
    }

    private function resetMensajes(): void
    {
        $this->mensajeExito = null;
        $this->mensajeError = null;
    }

    #[Layout('components.app-layout')]
    public function render()
    {
        return view('livewire.admin.cambiar-contrasena');
    }
}
