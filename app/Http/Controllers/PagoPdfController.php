<?php

namespace App\Http\Controllers;

use App\Livewire\Admin\MisPagosManager;
use App\Support\PagoPdfToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PagoPdfController extends Controller
{
    public function __invoke(string $token): Response
    {
        $clave = PagoPdfToken::resolver($token);

        if (! $clave) {
            abort(404, 'Comprobante no encontrado.');
        }
        /*
        |--------------------------------------------------------------------------
        | Obtener los pagos utilizando exactamente la misma lógica
        | de seguridad del componente MisPagosManager
        |--------------------------------------------------------------------------
        */

        $manager = app(MisPagosManager::class);

        $pagos = $manager->pagosParaPdf();

        $pago = $pagos->firstWhere('clave', $clave);

        /*
        |--------------------------------------------------------------------------
        | Si el comprobante no pertenece al usuario autenticado,
        | no se permite generar el PDF.
        |--------------------------------------------------------------------------
        */

        if (! $pago) {
            abort(404, 'Comprobante no encontrado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Tamaño del ticket
        |
        | 226.772 puntos ≈ 80 mm
        | 841.89 puntos ≈ 297 mm
        |--------------------------------------------------------------------------
        */

        $customPaper = [
            0,
            0,
            226.772,
            841.89
        ];

        /*
        |--------------------------------------------------------------------------
        | Generar PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'pdf.constancia-pago',
            [
                'pagoSeleccionado' => $pago,
            ]
        )
            ->setPaper($customPaper, 'portrait')
            ->setOption('isRemoteEnabled', true);

        /*
        |--------------------------------------------------------------------------
        | STREAM
        |
        | Esto hace que el navegador pueda mostrar el PDF
        | directamente en un iframe.
        |--------------------------------------------------------------------------
        */

        $nombre = ($pago['tipo'] ?? 'pago') === 'reprogramacion'
            ? 'constancia-reprogramacion-' . $pago['nro_reprogramacion']
            : 'constancia-pago-' . $pago['nro_pedido'];

        return $pdf->stream($nombre . '.pdf');
    }
}