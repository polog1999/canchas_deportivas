<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReservaVoucherService
{
    /**
     * @return array<string, mixed>
     */
    public function datosDesdePago(Pago $pago): array
    {
        $pago->loadMissing([
            'transaccion.reserva.usuario.perfil',
            'transaccion.reserva.cancha.sede',
            'transaccion.reserva.cancha.deportes',
        ]);

        $transaccion = $pago->transaccion;
        $reserva = $transaccion?->reserva;
        $titular = $reserva?->usuario;
        $perfil = $titular?->perfil;
        $cancha = $reserva?->cancha;
        $sede = $cancha?->sede;

        $horaInicio = $reserva?->hora_inicio;
        $horaFin = $reserva?->hora_fin;
        $duracionMin = ($horaInicio && $horaFin)
            ? (int) $horaInicio->diffInMinutes($horaFin)
            : 60;

        $deporte = data_get($transaccion?->respuesta_bruta, 'meta.deporte')
            ?? data_get($transaccion?->respuesta_bruta, 'reserva.deporte')
            ?? $cancha?->deportes?->first()?->nombre;

        $monto = round((float) $pago->monto, 2);
        $marca = trim((string) ($transaccion?->marca_tarjeta ?? ''));
        $tarjeta = trim((string) ($transaccion?->tarjeta_enmascarada ?? ''));

        if ($monto <= 0 || strtolower((string) $transaccion?->estado) === 'sin_pasarela') {
            $medioPago = 'Gratuito';
            $estado = 'Gratuito';
        } else {
            $medioPago = ($marca !== '' && $tarjeta !== '')
                ? trim($marca.' '.$tarjeta)
                : ($marca !== '' ? $marca : 'Tarjeta');
            $estado = 'Pagado';
        }

        $concepto = 'Reserva de cancha';
        if ($deporte) {
            $concepto .= ' · '.$deporte;
        }
        $concepto .= ' · '.$duracionMin.' min';
        if ($monto <= 0) {
            $concepto .= ' (cortesía)';
        }

        return [
            'id' => $pago->id,
            'nro_pedido' => (string) ($reserva?->id ?? $pago->id),
            'nro_operacion' => (string) ($transaccion?->transaccion_id ?? '—'),
            'codigo_voucher' => $reserva?->referencia_pago,
            'fecha_pago' => $this->formatearFechaPago($pago),
            'titular' => $titular?->nombreCompleto() ?? '—',
            'dni' => $perfil?->numero_documento ?? '—',
            'sede' => $sede?->nombre ?? '—',
            'cancha' => $cancha?->nombre ?? '—',
            'deporte' => $deporte ?? '—',
            'fecha_turno' => $this->formatearHoraTurno($horaInicio, 'd/m/Y'),
            'horario' => ($horaInicio && $horaFin)
                ? $this->formatearHoraTurno($horaInicio, 'H:i').' a '.$this->formatearHoraTurno($horaFin, 'H:i').' hs'
                : '—',
            'concepto' => $concepto,
            'medio_pago' => $medioPago,
            'monto' => $monto,
            'estado' => $estado,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function datosDesdeReserva(Reserva $reserva, array $meta = []): array
    {
        $reserva->loadMissing([
            'usuario.perfil',
            'cancha.sede',
            'cancha.deportes',
            'transacciones.pagos',
        ]);

        $pago = $reserva->transacciones
            ->flatMap(fn ($t) => $t->pagos)
            ->sortByDesc('pagado_en')
            ->first();

        if ($pago) {
            return $this->datosDesdePago($pago);
        }

        $usuario = $reserva->usuario;
        $cancha = $reserva->cancha;
        $horaInicio = $reserva->hora_inicio;
        $horaFin = $reserva->hora_fin;
        $duracionMin = ($horaInicio && $horaFin)
            ? (int) $horaInicio->diffInMinutes($horaFin)
            : 60;

        $deporte = $meta['deporte'] ?? $cancha?->deportes?->first()?->nombre;
        $monto = round((float) $reserva->precio_total, 2);

        $concepto = 'Reserva de cancha';
        if ($deporte) {
            $concepto .= ' · '.$deporte;
        }
        $concepto .= ' · '.$duracionMin.' min';
        if ($monto <= 0) {
            $concepto .= ' (cortesía)';
        }

        return [
            'id' => $reserva->id,
            'nro_pedido' => (string) $reserva->id,
            'nro_operacion' => '—',
            'codigo_voucher' => $reserva->referencia_pago,
            'fecha_pago' => now('America/Lima')->format('d/m/Y H:i'),
            'titular' => $usuario?->nombreCompleto() ?? '—',
            'dni' => $usuario?->perfil?->numero_documento ?? '—',
            'sede' => $meta['club'] ?? $cancha?->sede?->nombre ?? '—',
            'cancha' => $meta['cancha'] ?? $cancha?->nombre ?? '—',
            'deporte' => $deporte ?? '—',
            'fecha_turno' => $this->formatearHoraTurno($horaInicio, 'd/m/Y'),
            'horario' => ($horaInicio && $horaFin)
                ? $this->formatearHoraTurno($horaInicio, 'H:i').' a '.$this->formatearHoraTurno($horaFin, 'H:i').' hs'
                : '—',
            'concepto' => $concepto,
            'medio_pago' => $monto <= 0 ? 'Gratuito' : 'Tarjeta',
            'monto' => $monto,
            'estado' => $monto <= 0 ? 'Gratuito' : 'Pagado',
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function generarPdf(array $datos): string
    {
        $this->asegurarAutoloadDompdf();

        $html = view('pdf.voucher-pago', [
            'voucher' => $datos,
            'logoBase64' => $this->logoBase64(),
        ])->render();

        /** @var object $options */
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot(public_path());

        /** @var object $dompdf */
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    private function asegurarAutoloadDompdf(): void
    {
        static $registrado = false;

        if (! $registrado) {
            $projectRoot = dirname(__DIR__, 2);
            $vendor = $projectRoot.DIRECTORY_SEPARATOR.'vendor';

            $map = [
                'Dompdf\\' => $vendor.DIRECTORY_SEPARATOR.'dompdf'.DIRECTORY_SEPARATOR.'dompdf'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR,
                'FontLib\\' => $vendor.DIRECTORY_SEPARATOR.'dompdf'.DIRECTORY_SEPARATOR.'php-font-lib'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'FontLib'.DIRECTORY_SEPARATOR,
                'Svg\\' => $vendor.DIRECTORY_SEPARATOR.'dompdf'.DIRECTORY_SEPARATOR.'php-svg-lib'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Svg'.DIRECTORY_SEPARATOR,
                'Masterminds\\' => $vendor.DIRECTORY_SEPARATOR.'masterminds'.DIRECTORY_SEPARATOR.'html5'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR,
            ];

            foreach ($map as $prefix => $dir) {
                if (! is_dir($dir)) {
                    throw new \RuntimeException(
                        "Falta la dependencia PDF en {$dir}. Ejecuta: composer install --ignore-platform-req=ext-oci8"
                    );
                }
            }

            spl_autoload_register(static function (string $class) use ($map): void {
                foreach ($map as $prefix => $dir) {
                    if (! str_starts_with($class, $prefix)) {
                        continue;
                    }

                    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
                    $file = $dir.$relative.'.php';
                    if (is_file($file)) {
                        require_once $file;
                    }

                    return;
                }
            }, true, true);

            // Forzar carga inmediata para fallar con el error real si falta algo.
            $optionsFile = $map['Dompdf\\'].'Options.php';
            $dompdfFile = $map['Dompdf\\'].'Dompdf.php';
            require_once $optionsFile;
            require_once $dompdfFile;

            $registrado = true;
        }

        if (! class_exists(\Dompdf\Dompdf::class, false) || ! class_exists(\Dompdf\Options::class, false)) {
            throw new \RuntimeException(
                'No se pudo cargar Dompdf desde '.dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'dompdf. Cierra todas las ventanas de "php artisan serve" (Ctrl+C), abre una nueva terminal en la carpeta del proyecto y ejecuta: php artisan serve'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function contenidoPdf(array $datos): string
    {
        return $this->generarPdf($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function descargar(array $datos): Response
    {
        return response($this->generarPdf($datos), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->nombreArchivo($datos).'"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function nombreArchivo(array $datos): string
    {
        $codigo = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($datos['codigo_voucher'] ?? $datos['nro_pedido'] ?? 'voucher'));

        return 'voucher-'.trim($codigo, '-').'.pdf';
    }

    private function formatearFechaPago(Pago $pago): string
    {
        $raw = $pago->getRawOriginal('pagado_en');
        if (! $raw) {
            return '—';
        }

        return Carbon::parse($raw, 'UTC')
            ->timezone('America/Lima')
            ->format('d/m/Y H:i');
    }

    private function formatearHoraTurno(?Carbon $fecha, string $formato): string
    {
        if (! $fecha) {
            return '—';
        }

        return $fecha->format($formato);
    }

    private function logoBase64(): ?string
    {
        $path = public_path('logo_municipal_negro.png');
        if (! is_readable($path)) {
            return null;
        }

        $contenido = file_get_contents($path);
        if ($contenido === false) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($contenido);
    }
}
