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
            'users',
            'payments',
            'payment_titles',
            'classrooms',
            'academic_years',
            'settings',
            'roles',
            'permissions',
            'conversations',
            'audit_logs',
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
