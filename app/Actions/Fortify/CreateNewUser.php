<?php

namespace App\Actions\Fortify;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): Usuario
    {
        Validator::make($input, [
            'usuario' => ['required', 'string', 'max:50', 'unique:usuarios,usuario'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'correo_electronico' => [
                'nullable',
                'string',
                'email',
                'max:150',
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $rolCliente = Rol::firstOrCreate(
            ['nombre' => 'cliente'],
            [
                'descripcion' => 'Cliente',
                'activo' => true,
            ]
        );

        return DB::transaction(function () use ($input, $rolCliente) {
            $usuario = Usuario::create([
                'rol_id' => $rolCliente->id,
                'usuario' => $input['usuario'],
                'correo_electronico' => $input['correo_electronico'] ?? null,
                'clave' => $input['password'],
                'activo' => true,
            ]);

            $usuario->perfil()->create([
                'nombres' => $input['nombres'],
                'apellido_paterno' => $input['apellido_paterno'],
                'apellido_materno' => $input['apellido_materno'],
            ]);

            return $usuario;
        });
    }
}
