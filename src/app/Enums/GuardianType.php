<?php

namespace App\Enums;

enum GuardianType: string
{
    case OrangTua = 'orang_tua';
    case Wali = 'wali';

    public function label(): string
    {
        return match ($this) {
            self::OrangTua => 'Orang Tua',
            self::Wali => 'Wali',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::OrangTua->value => self::OrangTua->label(),
            self::Wali->value => self::Wali->label(),
        ];
    }
}

enum AdmissionStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Enrolled = 'enrolled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::UnderReview => 'Sedang Ditinjau',
            self::Approved => 'Diterima',
            self::Rejected => 'Ditolak',
            self::Enrolled => 'Terdaftar',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::Pending->value => self::Pending->label(),
            self::UnderReview->value => self::UnderReview->label(),
            self::Approved->value => self::Approved->label(),
            self::Rejected->value => self::Rejected->label(),
            self::Enrolled->value => self::Enrolled->label(),
            self::Cancelled->value => self::Cancelled->label(),
        ];
    }
}
