<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $usuario = auth()->user();
        $primerMenu = $usuario?->menus()->first();

        if ($primerMenu) {
            return redirect()->to($primerMenu->url());
        }

        return redirect('/portal/users');
    }
}
