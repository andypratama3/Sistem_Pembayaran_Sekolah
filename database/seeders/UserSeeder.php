<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@superadmin.com'],
            [
                'id' => Str::uuid(),
                'name' => 'superadmin',
                'password' => bcrypt('superadmin'),
                'slug' => Str::slug('superadmin'),
            ]
        );

        $roleSuperadmin = Role::firstOrCreate(
            ['name' => 'superadmin'],
            ['guard_name' => 'web']
        );

        $superadmin->assignRole($roleSuperadmin);

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => Str::uuid(),
                'name' => 'admin',
                'password' => bcrypt('superadmin'),
                'slug' => Str::slug('admin'),
            ]
        );

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );

        $admin->assignRole($role);

        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'id' => Str::uuid(),
                'name' => 'user',
                'password' => bcrypt('superadmin'),
                'slug' => Str::slug('user'),
            ]
        );

    }
}
