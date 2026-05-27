<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'superadmin',
            'admin',
            'admin_akademik',
            'hr',
            'finance',
            'teacher',
            'student',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['id' => (string) Str::uuid(), 'guard_name' => 'web']
            );
        }

        $superadmin = Role::where('name', 'superadmin')->first();
        $admin = Role::where('name', 'admin')->first();
        $teacher = Role::where('name', 'teacher')->first();
        $student = Role::where('name', 'student')->first();

        $resources = [
            'dashboard',
            'students',
            'teachers',
            'employees',
            'users',
            'grades',
            'payments',
            'payroll',
            'salary_grades',
            'education_allowances',
            'structural_allowances',
            'functional_allowances',
            'payroll_salary_rates',
            'payment_titles',
            'subjects',
            'classrooms',
            'academic_years',
            'academic_calendars',
            'attendances',
            'p5_assessments',
            'early_warning',
            'templates',
            'documents',
            'promotions',
            'bulk_operations',
            'settings',
            'roles',
            'permissions',
            'conversations',
            'audit_logs',
            'tasks',
            'articles',
            'categories',
            'achievements',
            'galleries',
            'heroes',
            'cooperations',
            'kml',
            'leave-requests',
            'employee-attendance',
            'settings',
            'cctv',
            'search',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];
        $permissionNames = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $name = "{$action}-{$resource}";
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ], [
                    'id' => (string) Str::uuid(),
                ]);
                $permissionNames[] = $name;
            }
        }

        // Keep test and local environments consistent with ResourceController middleware.
        $superadmin->syncPermissions($permissionNames);
        $admin->syncPermissions($permissionNames);
    }
}
