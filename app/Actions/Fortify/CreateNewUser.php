<?php

namespace App\Actions\Fortify;

use App\Models\Rol;
use App\Models\Usuario;
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
            'nombre' => ['required', 'string', 'max:150'],
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

        return Usuario::create([
            'rol_id' => $rolCliente->id,
            'usuario' => $input['usuario'],
            'nombre' => $input['nombre'],
            'correo_electronico' => $input['correo_electronico'] ?? null,
            'clave' => $input['password'],
            'activo' => true,
        ]);
    }
}
