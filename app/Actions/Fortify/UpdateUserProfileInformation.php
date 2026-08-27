<?php

namespace App\Actions\Fortify;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(Usuario $user, array $input): void
    {
        Validator::make($input, [
            'nombres' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'correo_electronico' => [
                'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo_electronico')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if (($input['correo_electronico'] ?? null) !== $user->correo_electronico &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'correo_electronico' => $input['correo_electronico'] ?? null,
            ])->save();
        }

        $user->perfil()->updateOrCreate(
            ['usuario_id' => $user->id],
            [
                'nombres' => $input['nombres'],
                'apellido_paterno' => $input['apellido_paterno'],
                'apellido_materno' => $input['apellido_materno'],
            ]
        );
    }

    /**
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(Usuario $user, array $input): void
    {
        $user->forceFill([
            'correo_electronico' => $input['correo_electronico'] ?? null,
            'correo_verificado_en' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
