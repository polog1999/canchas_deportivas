<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case SUPERADMIN = 'SUPERADMIN';
    case CLIENTE = 'CLIENTE';
   

   
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::SUPERADMIN => 'Super Administrador',
            self::CLIENTE => 'Cliente'
        };
    }
}