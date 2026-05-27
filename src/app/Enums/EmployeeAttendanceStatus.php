<?php

namespace App\Enums;

enum EmployeeAttendanceStatus: string
{
    case Hadir = 'hadir';
    case Cuti = 'cuti';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alpha = 'alpha';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Cuti => 'Cuti',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Alpha => 'Alpha (Tanpa Keterangan)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::Hadir->value => self::Hadir->label(),
            self::Cuti->value => self::Cuti->label(),
            self::Izin->value => self::Izin->label(),
            self::Sakit->value => self::Sakit->label(),
            self::Alpha->value => self::Alpha->label(),
        ];
    }
}
