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
        $id = PagoPdfToken::resolver($token);

        if (! $id) {
            abort(404, 'Pago no encontrado.');
        }
        /*
        |--------------------------------------------------------------------------
        | Obtener los pagos utilizando exactamente la misma lógica
        | de seguridad del componente MisPagosManager
        |--------------------------------------------------------------------------
        */

        $manager = app(MisPagosManager::class);

        $pagos = $manager->pagosParaPdf();

        $pago = $pagos->firstWhere('id', $id);

        /*
        |--------------------------------------------------------------------------
        | Si el pago no pertenece al usuario autenticado,
        | no se permite generar el PDF.
        |--------------------------------------------------------------------------
        */

        if (! $pago) {
            abort(404, 'Pago no encontrado.');
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

        return $pdf->stream(
            'constancia-pago-' . $pago['nro_pedido'] . '.pdf'
        );
    }
}