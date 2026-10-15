<?php

namespace Database\Seeders;

use App\Models\Spa;
use Illuminate\Database\Seeder;

class ProductionSpaSeeder extends Seeder
{
    public function run(): void
    {
        Spa::firstOrCreate(['slug' => 'spa-ai'], [
            'name' => 'Spa AI', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '10:00', 'closing_time' => '20:00',
            'public_status' => 'published',
        ]);
    }
}
