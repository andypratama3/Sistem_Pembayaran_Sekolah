<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            [
                'name' => 'SPP Bulanan',
                'code' => 'SPP',
                'slug' => 'spp-bulanan',
            ],
            [
                'name' => 'Uang Pangkal',
                'code' => 'PANGKAL',
                'slug' => 'uang-pangkal',
            ],
            [
                'name' => 'Uang Seragam',
                'code' => 'SERAGAM',
                'slug' => 'uang-seragam',
            ],
            [
                'name' => 'Uang Buku',
                'code' => 'BUKU',
                'slug' => 'uang-buku',
            ],
        ];

        foreach ($titles as $title) {
            $exists = DB::table('payment_titles')
                ->where('code', $title['code'])
                ->exists();

            if (! $exists) {
                DB::table('payment_titles')->insert(array_merge(
                    ['id' => Str::uuid()],
                    $title,
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ));
            }
        }
    }
}
