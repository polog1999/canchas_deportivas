<?php

namespace App\Http\Controllers;

use App\Models\Deporte;
use App\Models\Sede;
use App\Models\Slider;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sedes = Sede::query()
            ->where('esta_activo', true)
            ->with([
                'canchas' => fn ($q) => $q
                    ->where('esta_activo', true)
                    ->with('deportes'),
            ])
            ->orderBy('nombre')
            ->get();

        $deportes = Deporte::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $slides = Slider::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('welcome', compact('sedes', 'deportes', 'slides'));
    }
}
