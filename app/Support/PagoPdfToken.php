<?php

namespace App\Support;

class PagoPdfToken
{
    public static function generar(int $pagoId): string
    {
        return strtr(encrypt((string) $pagoId), [
            '+' => '-',
            '/' => '_',
            '=' => '',
        ]);
    }

    public static function resolver(string $token): ?int
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

            $id = decrypt($normalizado);

            return is_numeric($id) ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
