<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'nisn' => fake()->unique()->numerify('##########'),
            'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'phone' => fake()->phoneNumber(),
            'province_id' => fake()->numerify('##'),
            'regency_id' => fake()->numerify('####'),
            'district_id' => fake()->numerify('######'),
            'village_id' => fake()->numerify('##########'),
            'spp' => fake()->numberBetween(100000, 500000),
            'dpp' => fake()->numberBetween(1000000, 5000000),
            'uniform_fee' => fake()->numberBetween(200000, 500000),
            'va_number' => fake()->numerify('8##########'),
            'previous_school_name' => fake()->company(),
            'previous_school_address' => fake()->address(),
            'entry_year' => fake()->year(),
            'entry_date' => fake()->date(),
            'scholarship' => fake()->boolean(),
            'guardian_type' => fake()->randomElement(['orang_tua', 'wali']),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'father_education' => fake()->randomElement(['SD', 'SMP', 'SMA', 'S1', 'S2']),
            'mother_education' => fake()->randomElement(['SD', 'SMP', 'SMA', 'S1', 'S2']),
            'father_occupation' => fake()->jobTitle(),
            'mother_occupation' => fake()->jobTitle(),
            'guardian_name' => fake()->name(),
            'guardian_occupation' => fake()->jobTitle(),
            'guardian_address' => fake()->address(),
            'rt' => fake()->numerify('#'),
            'rw' => fake()->numerify('#'),
            'province_id' => fake()->numerify('##'),
            'regency_id' => fake()->numerify('####'),
            'district_id' => fake()->numerify('######'),
            'village_id' => fake()->numerify('##########'),
            'street' => fake()->streetAddress(),
            'residence_type' => fake()->randomElement(['milik_sendiri', 'sewa', 'menumpang']),
            'phone' => fake()->phoneNumber(),
            'parent_phone' => fake()->phoneNumber(),
            'parent_email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'slug' => fake()->unique()->slug(2),
            'dpp_status' => fake()->randomElement(['paid', 'unpaid']),
            'status' => fake()->randomElement(['baru', 'terdaftar', 'diterima', 'aktif', 'pindahan', 'graduated', 'alumni', 'transfer_out', 'dropped']),
            'phone_verified' => fake()->boolean(),
            'phone_verified_at' => fake()->optional()->dateTime(),
        ];
    }
}
