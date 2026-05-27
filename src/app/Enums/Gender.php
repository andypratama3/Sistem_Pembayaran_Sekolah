<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'Laki-laki';
    case Female = 'Perempuan';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Laki-laki',
            self::Female => 'Perempuan',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
