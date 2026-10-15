<?php

namespace Database\Seeders;

use App\Models\Spa;
use Illuminate\Database\Seeder;

class SpaSeeder extends Seeder
{
    public const NAME = 'Spa AI';

    public function run(): void
    {
        Spa::firstOrCreate(['slug' => 'spa-ai'], [
            'slug' => 'spa-ai',
            'name' => 'Spa AI',
            'description' => 'Sanctuary demo dengan ritual perawatan tubuh, pijat aromaterapi, dan ruang privat bernuansa taman.',
            'translations' => [
                'en' => [
                    'description' => 'A demo sanctuary for botanical body rituals, aromatherapy massage and quiet garden rooms.',
                ],
                'ja' => [
                    'description' => 'ボタニカルなボディケア、アロママッサージ、静かな個室をご紹介するデモスパです。',
                ],
            ],
            'address' => 'Lokasi demonstrasi — isi alamat spa Anda',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'phone' => null,
            'whatsapp' => null,
            'email' => 'hello@spaai.test',
            'instagram' => null,
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'default_locale' => 'id',
            'opening_time' => '10:00',
            'closing_time' => '20:00',
            'public_status' => 'published',
        ]);
    }
}
