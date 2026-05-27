<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! function_exists('get_system_config')) {
    /**
     * Get system configuration value from 'system_configs' table.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $default  Default value if key not found
     */
    function get_system_config(string $key, mixed $default = null): mixed
    {
        try {
            // Use cache to avoid frequent database hits
            return Cache::remember("system_config_{$key}", now()->addHours(24), function () use ($key, $default) {
                if (! Schema::hasTable('system_configs')) {
                    return $default;
                }

                $config = DB::table('system_configs')
                    ->where('key', $key)
                    ->first();

                if ($config) {
                    $nilai = $config->nilai;

                    // Type casting based on 'tipe' column
                    switch ($config->tipe) {
                        case 'boolean':
                            return (bool) $nilai;
                        case 'number':
                            return is_numeric($nilai) ? (float) $nilai : $nilai;
                        case 'json':
                            return json_decode($nilai, true);
                        default:
                            return $nilai;
                    }
                }

                return $default;
            });
        } catch (Exception $e) {
            // Fallback to default in case of any database/cache errors
            return $default;
        }
    }
}
