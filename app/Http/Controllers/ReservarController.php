<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Distrito;
use App\Models\Perfil;
use App\Models\Sede;
use App\Services\OcupacionReservasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ReservarController extends Controller
{
    public function index(): RedirectResponse
    {
        // Esa pantalla de listado de complejos ya no forma parte del flujo
        return redirect('/');
    }

    public function deporte(Request $request): View|RedirectResponse
    {
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
    }

    public function ocupacion(Request $request, OcupacionReservasService $ocupacion): JsonResponse
    {
        $sedeId = (int) $request->query('sede', 0);
        $deporteId = (int) $request->query('deporte_id', 0);
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fecha)) {
            return response()->json(['ok' => false, 'mensaje' => 'Fecha inválida.'], 422);
        }

        $canchaIds = Cancha::query()
            ->where('sede_id', $sedeId)
            ->where('esta_activo', true)
            ->when($deporteId > 0, fn ($q) => $q->whereHas('deportes', fn ($d) => $d->where('deportes.id', $deporteId)))
            ->pluck('id');

        return response()->json([
            'ok' => true,
            'fecha' => $fecha,
            'ocupados' => $ocupacion->porCancha($canchaIds, $fecha),
        ]);
    }

    public function confirmar(): View
    {
        $distritos = Distrito::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('reservar-confirmar', compact('distritos'));
    }

    public function pago(): View
    {
        return view('reservar-pago');
    }

    public function buscarDocumento(Request $request): JsonResponse
    {
        $documento = preg_replace('/\D+/', '', (string) $request->query('documento', ''));

        if (strlen($documento) < 8) {
            return response()->json([
                'valido' => false,
                'existe' => false,
                'mensaje' => 'Ingresa un documento válido (mínimo 8 dígitos).',
            ]);
        }

        $existe = Perfil::query()
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
    }

    public function verificarAcceso(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documento' => 'required|string|min:8|max:15',
            'clave' => 'required|string|min:4',
        ]);

        $documento = preg_replace('/\D+/', '', $data['documento']);

        $perfil = Perfil::query()
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
            if (Hash::isHashed($claveAlmacenada)) {
                $claveValida = Hash::check($data['clave'], $claveAlmacenada);
            } else {
                // Compatibilidad con claves legacy (texto plano en BD)
                $claveValida = hash_equals($claveAlmacenada, $data['clave']);
            }
        }

        if (! $claveValida) {
            return response()->json(['ok' => false, 'mensaje' => 'Contraseña incorrecta.'], 422);
        }

        Auth::login($usuario);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Acceso verificado.',
            'redirect' => route('dashboard'),
        ]);
    }
}
