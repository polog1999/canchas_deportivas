<?php

namespace App\Enums;

enum DocumentType:string
{
    case DNI = 'DNI';
    case CE = 'CE';
    case PAS = 'PAS';
   

   
    public function label(): string
    {
        return match($this) {
            self::DNI => 'Documento de identidad ',
            self::CE => 'Carné de extranjería',
            self::PAS => 'Pasaporte'
        };
    }
}
