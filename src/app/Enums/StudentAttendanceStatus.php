<?php

namespace App\Enums;

enum StudentAttendanceStatus: string
{
    case Hadir = 'hadir';
    case Izin = 'izin';
    case Pulang = 'pulang';
    case Sakit = 'sakit';
    case Alpa = 'alpa';
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Izin => 'Izin',
            self::Pulang => 'Pulang',
            self::Sakit => 'Sakit',
            self::Alpa => 'Alpa (Tanpa Keterangan)',
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Telat',
            self::Excused => 'Disetujui (Izin)',
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
            self::Izin->value => self::Izin->label(),
            self::Sakit->value => self::Sakit->label(),
            self::Alpa->value => self::Alpa->label(),
            self::Pulang->value => self::Pulang->label(),
            self::Late->value => self::Late->label(),
            self::Excused->value => self::Excused->label(),
        ];
    }
}
