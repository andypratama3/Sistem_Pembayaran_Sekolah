<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\PayrollSalaryRatesSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        // Ensure broadcasting doesn't attempt network calls during tests
        config(['broadcasting.default' => 'log']);

        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $this->seed(RoleSeeder::class);
        }

        // Ensure payroll salary rates are seeded for salary calculation tests
        if (Schema::hasTable('payroll_salary_rates')) {
            $this->seed(PayrollSalaryRatesSeeder::class);
        }
    }

    /**
     * Seed roles and permissions for testing.
     * Call this method in test methods that need roles.
     */
    protected function seedRoles(): void
    {
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $this->seed(RoleSeeder::class);
        }
    }

    protected function afterRefreshingDatabase()
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }
}
