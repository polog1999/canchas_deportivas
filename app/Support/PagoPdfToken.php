<?php

namespace App\Support;

/**
 * Token cifrado que identifica un comprobante en la URL del PDF.
 *
 * La clave interna tiene la forma "pago-12" o "repro-3", para distinguir la
 * constancia de pago de la de reprogramación sin exponer los ids.
 */
class PagoPdfToken
{
    public static function generar(int|string $clave): string
    {
        return strtr(encrypt((string) $clave), [
            '+' => '-',
            '/' => '_',
            '=' => '',
        ]);
    }

    public static function claveDePago(int $pagoId): string
    {
        return 'pago-'.$pagoId;
    }

    public static function claveDeReprogramacion(int $reprogramacionId): string
    {
        return 'repro-'.$reprogramacionId;
    }

    /**
     * Clave del comprobante, o null si el token no es válido.
     */
    public static function resolver(string $token): ?string
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        try {
            $normalizado = strtr($token, [
                '-' => '+',
                '_' => '/',
            ]);

            $resto = strlen($normalizado) % 4;

            if ($resto > 0) {
                $normalizado .= str_repeat('=', 4 - $resto);
            }

            $valor = (string) decrypt($normalizado);
        } catch (\Throwable) {
            return null;
        }

        // Tokens emitidos antes de que existieran las reprogramaciones.
        if (is_numeric($valor)) {
            return self::claveDePago((int) $valor);
        }

        return preg_match('/^(pago|repro)-[1-9]\d*$/', $valor) === 1 ? $valor : null;
    }
}
