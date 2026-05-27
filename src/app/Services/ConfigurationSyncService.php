<?php

namespace App\Services;

use App\Events\AcademicYearConfigUpdated;
use App\Events\EmployeeSalaryConfigUpdated;
use App\Helpers\DatabaseSchemaHelper;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EmployeeSalaryConfiguration;
use App\Models\Schedule;
use App\Models\SystemConfig as SystemConfigModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ConfigurationSyncService
 *
 * Memastikan ketika ada update konfigurasi sistem, semua data terkait
 * terupdate dengan konsisten dan tersinkron.
 *
 * Fitur:
 * - Sync academic year configuration ke semua dependent models
 * - Sync employee salary configuration ke payroll calculations
 * - Sync system config ke external integrations
 * - Validate consistency setelah update
 * - Rollback jika ada error
 */
class ConfigurationSyncService
{
    /**
     * Update konfigurasi sistem dan sinkronisasi ke semua data terkait
     *
     * @param  array  $configs  Format: ['id' => 'value', ...]
     * @return array ['success' => bool, 'synced' => [], 'errors' => []]
     */
    public function syncSystemConfigs(array $configs): array
    {
        $result = [
            'success' => false,
            'synced' => [],
            'errors' => [],
            'affected_models' => [],
        ];

        try {
            DB::beginTransaction();

            // 1. Update system_configs table
            $updatedConfigs = $this->updateSystemConfigs($configs);
            $result['synced']['system_configs'] = count($updatedConfigs);

            // 2. Clear cache untuk setiap config yang diupdate
            foreach ($updatedConfigs as $config) {
                Cache::forget("system_config_{$config->key}");
            }
            $result['synced']['cache_cleared'] = count($updatedConfigs);

            // 3. Identifikasi config yang berubah dan trigger sync
            foreach ($updatedConfigs as $config) {
                $this->handleConfigChange($config, $result);
            }

            DB::commit();
            $result['success'] = true;

            Log::info('Configuration sync completed', [
                'synced' => $result['synced'],
                'affected_models' => $result['affected_models'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $result['errors'][] = $e->getMessage();
            Log::error('Configuration sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $result;
    }

    /**
     * Sync academic year configuration ke semua dependent models
     */
    public function syncAcademicYearConfig(AcademicYear $academicYear): array
    {
        $result = [
            'success' => false,
            'synced' => [],
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            // 1. Jika tahun akademik diset aktif, deaktifkan yang lain
            if ($academicYear->is_active) {
                $deactivated = AcademicYear::where('is_active', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);

                $result['synced']['deactivated_other_years'] = $deactivated;
            }

            // 2. Validasi kalender akademik
            $calendars = $academicYear->calendars()->get();
            foreach ($calendars as $calendar) {
                $this->validateAcademicCalendar($calendar);
            }
            $result['synced']['validated_calendars'] = count($calendars);

            // 3. Validasi jadwal
            $schedules = $academicYear->schedules()->get();
            foreach ($schedules as $schedule) {
                $this->validateSchedule($schedule);
            }
            $result['synced']['validated_schedules'] = count($schedules);

            // 4. Validasi kelas
            $classrooms = $academicYear->classrooms()->get();
            foreach ($classrooms as $classroom) {
                $this->validateClassroom($classroom);
            }
            $result['synced']['validated_classrooms'] = count($classrooms);

            // 5. Trigger event untuk notify services
            event(new AcademicYearConfigUpdated($academicYear));

            DB::commit();
            $result['success'] = true;

            Log::info('Academic year config synced', [
                'academic_year_id' => $academicYear->id,
                'synced' => $result['synced'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $result['errors'][] = $e->getMessage();
            Log::error('Academic year config sync failed', [
                'academic_year_id' => $academicYear->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Sync employee salary configuration ke payroll calculations
     */
    public function syncEmployeeSalaryConfig(EmployeeSalaryConfiguration $salaryConfig): array
    {
        $result = [
            'success' => false,
            'synced' => [],
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            // 1. Deaktifkan config lama untuk employee yang sama di tahun yang sama
            if ($salaryConfig->is_active) {
                $deactivated = EmployeeSalaryConfiguration::where('employee_id', $salaryConfig->employee_id)
                    ->where('academic_year', $salaryConfig->academic_year)
                    ->where('id', '!=', $salaryConfig->id)
                    ->update(['is_active' => false]);

                $result['synced']['deactivated_old_configs'] = $deactivated;
            }

            // 2. Validasi effective date
            if ($salaryConfig->effective_date > now()->toDateString()) {
                Log::warning('Salary config effective date is in future', [
                    'employee_id' => $salaryConfig->employee_id,
                    'effective_date' => $salaryConfig->effective_date,
                ]);
            }

            // 3. Invalidate payroll calculations yang sudah dibuat
            $this->invalidatePayrollCalculations(
                $salaryConfig->employee_id,
                $salaryConfig->academic_year
            );
            $result['synced']['invalidated_payrolls'] = true;

            // 4. Trigger event untuk notify payroll service
            event(new EmployeeSalaryConfigUpdated($salaryConfig));

            DB::commit();
            $result['success'] = true;

            Log::info('Employee salary config synced', [
                'employee_id' => $salaryConfig->employee_id,
                'academic_year' => $salaryConfig->academic_year,
                'synced' => $result['synced'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $result['errors'][] = $e->getMessage();
            Log::error('Employee salary config sync failed', [
                'employee_id' => $salaryConfig->employee_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Validate consistency dari semua konfigurasi
     *
     * @return array ['valid' => bool, 'issues' => []]
     */
    public function validateAllConfigurations(): array
    {
        $issues = [];

        // 1. Validasi hanya satu tahun akademik yang aktif
        $activeYears = AcademicYear::where('is_active', true)->count();
        if ($activeYears > 1) {
            $issues[] = "Multiple active academic years found: {$activeYears}";
        } elseif ($activeYears === 0) {
            $issues[] = 'No active academic year found';
        }

        // 2. Validasi kalender akademik
        $invalidCalendars = $this->findInvalidCalendars();
        if (! empty($invalidCalendars)) {
            $issues[] = 'Invalid academic calendars: '.implode(', ', $invalidCalendars);
        }

        // 3. Validasi jadwal
        $invalidSchedules = $this->findInvalidSchedules();
        if (! empty($invalidSchedules)) {
            $issues[] = 'Invalid schedules: '.implode(', ', $invalidSchedules);
        }

        // 4. Validasi konfigurasi gaji
        $invalidSalaryConfigs = $this->findInvalidSalaryConfigs();
        if (! empty($invalidSalaryConfigs)) {
            $issues[] = 'Invalid salary configurations: '.implode(', ', $invalidSalaryConfigs);
        }

        // 5. Validasi system config cache
        $cacheIssues = $this->validateSystemConfigCache();
        if (! empty($cacheIssues)) {
            $issues = array_merge($issues, $cacheIssues);
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'timestamp' => now(),
        ];
    }

    /**
     * Get configuration sync status
     */
    public function getSyncStatus(): array
    {
        return [
            'academic_years' => [
                'total' => AcademicYear::count(),
                'active' => AcademicYear::where('is_active', true)->count(),
                'last_updated' => AcademicYear::latest('updated_at')->first()?->updated_at,
            ],
            'academic_calendars' => [
                'total' => AcademicCalendar::count(),
                'published' => AcademicCalendar::where('is_published', true)->count(),
                'last_updated' => AcademicCalendar::latest('updated_at')->first()?->updated_at,
            ],
            'salary_configurations' => [
                'total' => EmployeeSalaryConfiguration::count(),
                'active' => EmployeeSalaryConfiguration::where('is_active', true)->count(),
                'last_updated' => EmployeeSalaryConfiguration::latest('updated_at')->first()?->updated_at,
            ],
            'system_configs' => [
                'total' => SystemConfigModel::count(),
                'last_updated' => SystemConfigModel::latest('updated_at')->first()?->updated_at,
            ],
            'validation' => $this->validateAllConfigurations(),
        ];
    }

    // ============ PRIVATE METHODS ============

    /**
     * Update system_configs table dengan bulk update
     */
    private function updateSystemConfigs(array $configs): array
    {
        $updated = [];
        $cases = [];
        $ids = [];
        $params = [];

        foreach ($configs as $id => $nilai) {
            $ids[] = $id;
            $cases[] = 'WHEN id = ? THEN ?';
            $params[] = $id;
            $params[] = $nilai;
        }

        if (! empty($ids)) {
            $idsString = implode(',', array_fill(0, count($ids), '?'));
            $caseString = implode(' ', $cases);

            DB::update(
                "UPDATE system_configs SET nilai = CASE {$caseString} END WHERE id IN ({$idsString})",
                array_merge($params, $ids)
            );

            // Fetch updated configs
            $updated = DB::table('system_configs')
                ->whereIn('id', $ids)
                ->get()
                ->toArray();
        }

        return $updated;
    }

    /**
     * Handle perubahan config tertentu
     */
    private function handleConfigChange(object $config, array &$result): void
    {
        $key = $config->key;
        $grup = $config->grup;

        // Sync: ketika academic_year diubah, aktifkan tahun akademik yg dipilih
        if ($key === 'academic_year') {
            $yearName = $config->nilai;
            $academicYear = AcademicYear::where('name', $yearName)->first();

            if ($academicYear) {
                $academicYear->is_active = true;
                $academicYear->save();

                // Deactivate other academic years
                AcademicYear::where('is_active', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);

                $this->syncAcademicYearConfig($academicYear);
                $result['affected_models'][] = 'AcademicYear';
            }

            return;
        }

        // Trigger sync berdasarkan grup config
        switch ($grup) {
            case 'payroll':
                $result['affected_models'][] = 'EmployeeSalaryConfiguration';
                $result['affected_models'][] = 'EmployeePayroll';
                break;

            case 'absensi':
                $result['affected_models'][] = 'Attendance';
                break;
        }
    }

    /**
     * Validate academic calendar
     */
    private function validateAcademicCalendar(AcademicCalendar $calendar): void
    {
        // Check if columns exist
        if (! DatabaseSchemaHelper::columnsExist('academic_calendars', ['start_date', 'end_date'])) {
            return;
        }

        if ($calendar->start_date >= $calendar->end_date) {
            throw new \Exception("Invalid calendar dates for {$calendar->id}");
        }

        if ($calendar->academicYear && $calendar->start_date < $calendar->academicYear->start_date) {
            throw new \Exception('Calendar start date before academic year start');
        }
    }

    /**
     * Validate schedule
     */
    private function validateSchedule(Schedule $schedule): void
    {
        // Check if columns exist
        if (! DatabaseSchemaHelper::columnsExist('schedules', ['start_time', 'end_time'])) {
            return;
        }

        if ($schedule->start_time >= $schedule->end_time) {
            throw new \Exception("Invalid schedule times for {$schedule->id}");
        }
    }

    /**
     * Validate classroom
     */
    private function validateClassroom(Classroom $classroom): void
    {
        if (! $classroom->academic_year_id) {
            throw new \Exception("Classroom {$classroom->id} missing academic_year_id");
        }
    }

    /**
     * Find invalid academic calendars
     */
    private function findInvalidCalendars(): array
    {
        // Check if table and columns exist
        if (! DatabaseSchemaHelper::columnsExist('academic_calendars', ['start_date', 'end_date'])) {
            return [];
        }

        return DB::table('academic_calendars')
            ->whereRaw('start_date >= end_date')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Find invalid schedules
     */
    private function findInvalidSchedules(): array
    {
        // Check if table and columns exist
        if (! DatabaseSchemaHelper::columnsExist('schedules', ['start_time', 'end_time'])) {
            return [];
        }

        return DB::table('schedules')
            ->whereRaw('start_time >= end_time')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Find invalid salary configurations
     */
    private function findInvalidSalaryConfigs(): array
    {
        // Check if table and columns exist
        if (! DatabaseSchemaHelper::columnsExist('employee_salary_configurations', ['end_date', 'effective_date'])) {
            return [];
        }

        return DB::table('employee_salary_configurations')
            ->whereRaw('end_date IS NOT NULL AND end_date < effective_date')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Validate system config cache
     */
    private function validateSystemConfigCache(): array
    {
        $issues = [];
        $configs = SystemConfigModel::get();

        foreach ($configs as $config) {
            $cacheKey = "system_config_{$config->key}";
            $cached = Cache::get($cacheKey);

            if ($cached === null) {
                // Cache miss is ok, akan di-regenerate
                continue;
            }

            // Verify cache value matches database
            $dbValue = $config->castValue($config->getNilaiRawAttribute(), $config->tipe);
            if ($cached !== $dbValue) {
                $issues[] = "Cache mismatch for {$config->key}";
            }
        }

        return $issues;
    }

    /**
     * Cast config value berdasarkan type
     */

    /**
     * Invalidate payroll calculations
     */
    private function invalidatePayrollCalculations(string $employeeId, string $academicYear): void
    {
        // Mark payroll preparations as needs recalculation
        DB::table('payroll_preparations')
            ->where('employee_id', $employeeId)
            ->where('academic_year', $academicYear)
            ->update(['needs_recalculation' => true]);

        // Clear related cache
        Cache::forget("payroll_prep_{$employeeId}_{$academicYear}");
    }
}
