<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2024/2025',
                'start_date' => '2024-07-15',
                'end_date' => '2025-06-30',
                'is_active' => true,
            ],
            [
                'name' => '2023/2024',
                'start_date' => '2023-07-15',
                'end_date' => '2024-06-30',
                'is_active' => false,
            ],
            [
                'name' => '2025/2026',
                'start_date' => '2025-07-15',
                'end_date' => '2026-06-30',
                'is_active' => false,
            ],
        ];

        foreach ($academicYears as $year) {
            AcademicYear::firstOrCreate(
                ['name' => $year['name']],
                array_merge(['id' => Str::uuid()], $year)
            );
        }
    }
}
