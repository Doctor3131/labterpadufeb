<?php

namespace App\Enums;

enum CategoryEnum: string
{
    // Hardcoded categories dari requirement
    case PC = 'PC';
    case MONITOR = 'Monitor';
    case KEYBOARD = 'Keyboard';
    case MOUSE = 'Mouse';
    case TV = 'TV';
    case LAPTOP = 'Laptop';
    case PRINTER = 'Printer';
    case SCANNER = 'Scanner';
    case ROUTER = 'Router';
    case SWITCH = 'Switch';
    case AC = 'AC';
    case PROYEKTOR = 'Proyektor';
    case BRACKET_TV = 'Bracket TV';
    case MEJA = 'Meja';
    case KURSI = 'Kursi';
    
    /**
     * Get all category values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
