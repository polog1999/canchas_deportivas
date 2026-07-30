<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {

        $user = $request->user();
        $user->getRoleNames()->first();
        return redirect()->route('users');
        // $user = $request->user();

        // switch ($user->role->value) {
        //     case 'ADMIN':
        //         return redirect('/admin');

        //     case 'DOCENTE':
        //         return redirect('/docente');

        //     case 'ALUMNO':
        //         return redirect('/alumno');

        //     case 'ENCARGADO_SEDE':
        //         return redirect('/encargado-sede');

        //     case 'PADRE':
        //         return redirect('/apoderado');
        // }
    }
}
