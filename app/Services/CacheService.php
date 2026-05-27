<?php

namespace App\Services;

class CacheService
{
    public static function flushDashboard(): void
    {
        cache()->forget('dashboard_summary');
    }

    public static function flushCounts(): void
    {
        cache()->forget('dashboard_counts');
    }
}
