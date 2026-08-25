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
            'nombre' => ['required', 'string', 'max:150'],
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
                'nombre' => $input['nombre'],
                'correo_electronico' => $input['correo_electronico'] ?? null,
            ])->save();
        }
    }

    /**
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(Usuario $user, array $input): void
    {
        $user->forceFill([
            'nombre' => $input['nombre'],
            'correo_electronico' => $input['correo_electronico'] ?? null,
            'correo_verificado_en' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
