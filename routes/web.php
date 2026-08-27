<?php

use App\Livewire\Admin\MisPagosManager;
use App\Livewire\Admin\VerReservasManager;
use App\Livewire\Admin\CourtManager;
use App\Livewire\Admin\DeporteManager;
use App\Livewire\Admin\LocationManager;
use App\Livewire\Admin\MenuStructureManager;
use App\Livewire\Admin\RoleMenuManager;
use App\Livewire\Admin\TusneCatalogManager;
use App\Livewire\UserManagement;
use App\Models\Deporte;
use App\Models\Sede;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
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

    return view('welcome', compact('sedes', 'deportes'));
});

Route::get('/reservar', function (\Illuminate\Http\Request $request) {
    // Esa pantalla de listado de complejos ya no forma parte del flujo
    return redirect('/');
})->name('reservar');

Route::get('/reservar/deporte', function (\Illuminate\Http\Request $request) {
    $sedeId = (int) $request->query('sede', 0);
    $fecha = $request->query('fecha', now()->format('Y-m-d'));

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
        $fecha = now()->format('Y-m-d');
    }

    $sede = Sede::query()
        ->where('esta_activo', true)
        ->with([
            'canchas' => fn ($q) => $q
                ->where('esta_activo', true)
                ->with('deportes'),
        ])
        ->find($sedeId);

    if (! $sede) {
        return redirect('/');
    }

    $deportes = $sede->canchas
        ->flatMap(fn ($c) => $c->deportes->map(fn ($d) => [
            'deporte' => $d,
            'precio' => (float) $c->precio_por_hora,
        ]))
        ->groupBy(fn ($row) => $row['deporte']->id)
        ->map(function ($rows) {
            /** @var \App\Models\Deporte $deporte */
            $deporte = $rows->first()['deporte'];
            $precios = $rows->pluck('precio')->filter(fn ($p) => $p > 0);

            return [
                'id' => $deporte->id,
                'nombre' => $deporte->nombre,
                'imagen_url' => $deporte->urlImagen(),
                'canchas' => $rows->count(),
                'precioDesde' => $precios->isNotEmpty() ? (float) $precios->min() : 0,
            ];
        })
        ->sortBy('nombre')
        ->values();

    return view('reservar-deporte', [
        'sede' => $sede,
        'deportes' => $deportes,
        'fecha' => $fecha,
    ]);
})->name('reservar.deporte');

Route::get('/reservar/turno', function (\Illuminate\Http\Request $request) {
    $sedeId = (int) $request->query('sede', 0);
    $deporteId = (int) $request->query('deporte_id', 0);
    $fecha = $request->query('fecha', now()->format('Y-m-d'));

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
        $fecha = now()->format('Y-m-d');
    }

    // Sin deporte → deportes de la sede elegida
    if ($deporteId <= 0) {
        return redirect()->route('reservar.deporte', [
            'sede' => $sedeId,
            'fecha' => $fecha,
        ]);
    }

    $sede = Sede::query()
        ->where('esta_activo', true)
        ->with([
            'canchas' => function ($q) use ($deporteId) {
                $q->where('esta_activo', true)
                    ->with('deportes')
                    ->orderBy('nombre');

                if ($deporteId > 0) {
                    $q->whereHas('deportes', fn ($d) => $d->where('deportes.id', $deporteId));
                }
            },
        ])
        ->find($sedeId);

    if (! $sede) {
        return redirect()->route('reservar');
    }

    if ($deporteId > 0) {
        $deporteNombre = (string) (Deporte::query()->where('id', $deporteId)->value('nombre') ?? 'Deporte');
    } else {
        $deporteNombre = $sede->canchas
            ->flatMap(fn ($c) => $c->deportes->pluck('nombre'))
            ->unique()
            ->values()
            ->implode(' · ') ?: 'Deporte';
    }

    $canchaIds = $sede->canchas->pluck('id');
    $ocupadosPorCancha = ocupacionReservasPorCancha($canchaIds, $fecha);

    $sedeData = [
        'id' => $sede->id,
        'nombre' => $sede->nombre,
        'direccion' => $sede->direccion,
        'imagen' => $sede->urlImagen(),
        'hora_inicio' => $sede->hora_inicio ? substr((string) $sede->hora_inicio, 0, 5) : '08:00',
        'hora_fin' => $sede->hora_fin ? substr((string) $sede->hora_fin, 0, 5) : '22:00',
        'canchas' => $sede->canchas->map(fn ($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'detalle' => $c->deportes->pluck('nombre')->implode(' · ') ?: 'Cancha',
            'deporte_ids' => $c->deportes->pluck('id')->values(),
            'precio' => (float) $c->precio_por_hora,
            'ocupados' => $ocupadosPorCancha[$c->id] ?? [],
        ])->values(),
    ];

    return view('reservar-turno', [
        'sede' => $sedeData,
        'fecha' => $fecha,
        'deporte' => $deporteNombre,
        'deporte_id' => $deporteId > 0 ? $deporteId : null,
    ]);
})->name('reservar.turno');

Route::get('/reservar/ocupacion', function (\Illuminate\Http\Request $request) {
    $sedeId = (int) $request->query('sede', 0);
    $deporteId = (int) $request->query('deporte_id', 0);
    $fecha = $request->query('fecha', now()->format('Y-m-d'));

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
        return response()->json(['ok' => false, 'mensaje' => 'Fecha inválida.'], 422);
    }

    $canchaIds = \App\Models\Cancha::query()
        ->where('sede_id', $sedeId)
        ->where('esta_activo', true)
        ->when($deporteId > 0, fn ($q) => $q->whereHas('deportes', fn ($d) => $d->where('deportes.id', $deporteId)))
        ->pluck('id');

    return response()->json([
        'ok' => true,
        'fecha' => $fecha,
        'ocupados' => ocupacionReservasPorCancha($canchaIds, $fecha),
    ]);
})->name('reservar.ocupacion');

/**
 * Horas ocupadas por cancha según reservas (hora_inicio / hora_fin).
 * Un slot H:00–(H+1):00 está ocupado si solapa con alguna reserva no cancelada.
 *
 * @param  \Illuminate\Support\Collection<int, int>|array<int>  $canchaIds
 * @return array<int, list<int>>
 */
if (! function_exists('ocupacionReservasPorCancha')) {
    function ocupacionReservasPorCancha($canchaIds, string $fecha): array
    {
        $canchaIds = collect($canchaIds)->filter()->values();
        if ($canchaIds->isEmpty()) {
            return [];
        }

        $inicioDia = \Carbon\Carbon::parse($fecha)->startOfDay();
        $finDia = $inicioDia->copy()->endOfDay();

        $reservas = \App\Models\Reserva::query()
            ->whereIn('cancha_id', $canchaIds)
            ->where('hora_inicio', '<', $finDia)
            ->where('hora_fin', '>', $inicioDia)
            ->where(function ($q) {
                $q->whereNull('estado')
                    ->orWhereRaw('UPPER(estado) <> ?', ['CANCELADA']);
            })
            ->get(['cancha_id', 'hora_inicio', 'hora_fin']);

        $ocupados = [];
        foreach ($canchaIds as $id) {
            $ocupados[(int) $id] = [];
        }

        foreach ($reservas as $reserva) {
            $inicio = \Carbon\Carbon::parse($reserva->hora_inicio);
            $fin = \Carbon\Carbon::parse($reserva->hora_fin);

            for ($h = 0; $h < 24; $h++) {
                $slotInicio = $inicioDia->copy()->setTime($h, 0, 0);
                $slotFin = $slotInicio->copy()->addHour();

                if ($inicio->lt($slotFin) && $fin->gt($slotInicio)) {
                    $ocupados[(int) $reserva->cancha_id][] = $h;
                }
            }
        }

        foreach ($ocupados as $id => $horas) {
            $ocupados[$id] = array_values(array_unique($horas));
            sort($ocupados[$id]);
        }

        return $ocupados;
    }
}

Route::get('/reservar/confirmar', function () {
    $distritos = \App\Models\Distrito::query()
        ->orderBy('nombre')
        ->get(['id', 'nombre']);

    return view('reservar-confirmar', compact('distritos'));
})->name('reservar.confirmar');

Route::get('/reservar/pago', function () {
    return view('reservar-pago');
})->name('reservar.pago');

Route::get('/reservar/buscar-documento', function (\Illuminate\Http\Request $request) {
    $documento = preg_replace('/\D+/', '', (string) $request->query('documento', ''));

    if (strlen($documento) < 8) {
        return response()->json([
            'valido' => false,
            'existe' => false,
            'mensaje' => 'Ingresa un documento válido (mínimo 8 dígitos).',
        ]);
    }

    $existe = \App\Models\Perfil::query()
        ->where('numero_documento', $documento)
        ->whereHas('usuario', fn ($q) => $q->where('activo', true))
        ->exists();

    return response()->json([
        'valido' => true,
        'existe' => $existe,
        'mensaje' => $existe
            ? 'Usuario encontrado. Ingresa tu contraseña para continuar.'
            : 'Documento no registrado. Completa tus datos para continuar.',
    ]);
})->name('reservar.buscar-documento');

Route::post('/reservar/verificar-acceso', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'documento' => 'required|string|min:8|max:15',
        'clave' => 'required|string|min:4',
    ]);

    $documento = preg_replace('/\D+/', '', $data['documento']);

    $perfil = \App\Models\Perfil::query()
        ->with('usuario')
        ->where('numero_documento', $documento)
        ->whereHas('usuario', fn ($q) => $q->where('activo', true))
        ->first();

    if (! $perfil?->usuario) {
        return response()->json(['ok' => false, 'mensaje' => 'No se encontró un usuario con ese documento.'], 404);
    }

    $usuario = $perfil->usuario;
    // Usar valor crudo de BD (el cast 'hashed' no debe interferir en la comparación)
    $claveAlmacenada = (string) $usuario->getRawOriginal('clave');
    $claveValida = false;

    if ($claveAlmacenada !== '') {
        if (\Illuminate\Support\Facades\Hash::isHashed($claveAlmacenada)) {
            $claveValida = \Illuminate\Support\Facades\Hash::check($data['clave'], $claveAlmacenada);
        } else {
            // Compatibilidad con claves legacy (texto plano en BD)
            $claveValida = hash_equals($claveAlmacenada, $data['clave']);
        }
    }

    if (! $claveValida) {
        return response()->json(['ok' => false, 'mensaje' => 'Contraseña incorrecta.'], 422);
    }

    \Illuminate\Support\Facades\Auth::login($usuario);

    return response()->json([
        'ok' => true,
        'mensaje' => 'Acceso verificado.',
        'redirect' => route('dashboard'),
    ]);
})->name('reservar.verificar-acceso');

Route::middleware(['auth'])->get('/dashboard', function () {
    $usuario = auth()->user();
    $primerMenu = $usuario?->menus()->first();

    if ($primerMenu) {
        return redirect()->to($primerMenu->url());
    }

    return redirect('/portal/users');
})->name('dashboard');

Route::get('/prueba', function () {
    return view('prueba');
})->middleware(['auth'])->name('prueba');

Route::middleware(['auth'])->prefix('portal')->group(function () {
    // El permiso es la ruta/link del menú. El nombre (nombre) se puede cambiar libremente.
    Route::get('users', UserManagement::class)
        ->middleware('permission:/portal/users')
        ->name('users');

    Route::get('/tusne-catalog', TusneCatalogManager::class)
        ->middleware('permission:/portal/tusne-catalog')
        ->name('tusne.index');

    Route::get('/locations', LocationManager::class)
        ->middleware('permission:/portal/locations')
        ->name('locations.index');

    Route::get('/courts', CourtManager::class)
        ->middleware('permission:/portal/courts')
        ->name('courts.index');

    Route::get('/deportes', DeporteManager::class)
        ->middleware('permission:/portal/deportes')
        ->name('deportes.index');

    Route::get('/mis-pagos', MisPagosManager::class)
        ->middleware('permission:/portal/mis-pagos')
        ->name('mis-pagos.index');

    Route::get('/ver-reservas', VerReservasManager::class)
        ->middleware('permission:/portal/ver-reservas')
        ->name('ver-reservas.index');

    Route::get('/roles-menus', RoleMenuManager::class)
        ->middleware('permission:/portal/roles-menus')
        ->name('roles-menus.index');

    Route::get('/menus', MenuStructureManager::class)
        ->middleware('permission:/portal/menus')
        ->name('menus.index');
});
