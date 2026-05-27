<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'slug' => fake()->unique()->slug(2),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    public function teacher(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('teacher');

            // Create Employee record if not exists
            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $user->name ?? 'Teacher',
                    'sex' => 'Laki-Laki',
                    'nip' => 'NIP-'.strtoupper(Str::random(6)),
                    'slug' => $user->slug ?? Str::slug($user->name ?? 'teacher'),
                    'employee_id' => 'EMP-'.strtoupper(Str::random(6)),
                    'join_date' => now()->subYear(),
                    'status' => 1,
                ]
            );

            // Create Teacher record if not exists
            Teacher::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $user->name ?? 'Teacher',
                    'slug' => Str::slug($user->name ?? 'teacher').'-'.Str::random(6),
                    'specialization' => 'General',
                    'is_active' => true,
                ]
            );
        });
    }

    public function student(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('student');
        });
    }
}
