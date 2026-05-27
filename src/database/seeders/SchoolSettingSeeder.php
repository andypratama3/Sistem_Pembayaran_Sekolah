<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'school_name', 'value' => 'SMA Negeri 1 Jakarta'],
            ['key' => 'school_address', 'value' => 'Jl. Pendidikan No. 123, Jakarta'],
            ['key' => 'school_phone', 'value' => '021-12345678'],
            ['key' => 'school_email', 'value' => 'info@sma1jakarta.sch.id'],
            ['key' => 'school_website', 'value' => 'https://www.sma1jakarta.sch.id'],
            ['key' => 'school_logo', 'value' => '/images/logo.png'],
            ['key' => 'academic_year', 'value' => '2024/2025'],
            ['key' => 'max_class_capacity', 'value' => '36'],
            ['key' => 'minimum_attendance_percentage', 'value' => '75'],
            ['key' => 'late_payment_fine_percentage', 'value' => '2'],
        ];

        foreach ($settings as $setting) {
            DB::table('school_settings')->insertOrIgnore(array_merge($setting, [
                'id' => (string) Str::uuid(),
                'subscription_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
