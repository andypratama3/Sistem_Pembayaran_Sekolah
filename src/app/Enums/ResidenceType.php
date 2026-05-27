<?php

namespace App\Enums;

enum ResidenceType: string
{
    case RumahPribadi = 'Rumah Pribadi';
    case RumahSewa = 'Rumah Sewa';
    case Kos = 'Kos';
    case Asrama = 'Asrama';
    case Lainnya = 'Lainnya';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::RumahPribadi->value => 'Rumah Pribadi',
            self::RumahSewa->value => 'Rumah Sewa',
            self::Kos->value => 'Kos',
            self::Asrama->value => 'Asrama',
            self::Lainnya->value => 'Lainnya',
        ];
    }
}

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Completed => 'Lunas',
            self::Overdue => 'Jatuh Tempo',
            self::Cancelled => 'Dibatalkan',
            self::Failed => 'Gagal',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::Pending->value => 'Menunggu',
            self::Completed->value => 'Lunas',
            self::Overdue->value => 'Jatuh Tempo',
            self::Cancelled->value => 'Dibatalkan',
            self::Failed->value => 'Gagal',
        ];
    }
}
