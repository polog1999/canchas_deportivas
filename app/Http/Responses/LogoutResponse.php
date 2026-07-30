<?php

namespace App\Http\Responses;
use Laravel\Fortify\Contracts\LogoutResponse as CustomLogoutResponse;

class LogoutResponse implements CustomLogoutResponse{
    public function toResponse($request)
    {
        return redirect('login');
    }
}