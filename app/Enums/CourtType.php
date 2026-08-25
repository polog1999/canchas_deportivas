<?php

namespace App\Enums;

enum CourtType: string
{
    case FUTBOL_11 = 'Fútbol 11';
    case FUTBOL_7 = 'Fútbol 7';
    case FUTSAL = 'Futsal / Fulbito';
    case TENIS = 'Tenis';
    case BASQUET = 'Básquetbol';
    case VOLEY = 'Vóleibol';
    case PADEL = 'Pádel';
    case FRONT_PALOTA = 'Paleta Frontón';
    case MULTIUSO = 'Losa Multiuso';

    public function icon(): string
    {
        return match($this) {
            self::FUTBOL_11, self::FUTBOL_7, self::FUTSAL => 'fa-futbol',
            self::TENIS, self::PADEL, self::FRONT_PALOTA => 'fa-table-tennis-paddle-ball',
            self::BASQUET => 'fa-basketball',
            self::VOLEY => 'fa-volleyball',
            self::MULTIUSO => 'fa-trophy',
        };
    }
}