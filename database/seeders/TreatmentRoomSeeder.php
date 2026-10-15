<?php

namespace Database\Seeders;

use App\Models\Spa;
use App\Models\TreatmentRoom;
use Illuminate\Database\Seeder;

class TreatmentRoomSeeder extends Seeder
{
    public function run(): void
    {
        $spa = Spa::where('slug', 'spa-ai')->firstOrFail();
        $items = [[
            'slug' => 'serein-suite',
            'name' => 'Serein Suite',
            'description' => 'Ruang privat untuk satu tamu, dengan shower dan pencahayaan hangat.',
            'translations' => [
                'en' => [
                    'name' => 'Serein Suite',
                    'description' => 'A private room for one guest, with a shower and warm lighting.',
                ],
                'ja' => [
                    'name' => 'セランスイート',
                    'description' => '専用シャワーと温かな照明を備えた、お一人様向けの個室。',
                ],
            ],
            'room_type' => 'single',
            'min_guests' => 1,
            'max_guests' => 1,
            'features' => ['private_shower', 'air_conditioning', 'quiet', 'accessible'],
            'reservation_fee' => 0,
            'minimum_spend' => null,
        ], [
            'slug' => 'together-suite',
            'name' => 'Together Suite',
            'description' => 'Dua bed treatment dalam satu suite untuk menikmati ritual bersama.',
            'translations' => [
                'en' => [
                    'name' => 'Together Suite',
                    'description' => 'Two treatment beds in a shared suite for a ritual together.',
                ],
                'ja' => [
                    'name' => 'トゥギャザースイート',
                    'description' => '2台の施術ベッドを備えたスイートで、大切な方とご一緒に。',
                ],
            ],
            'room_type' => 'couple',
            'min_guests' => 2,
            'max_guests' => 2,
            'features' => ['couple_beds', 'private_shower', 'air_conditioning', 'quiet'],
            'reservation_fee' => 100000,
            'minimum_spend' => null,
        ], [
            'slug' => 'bamboo-pavilion',
            'name' => 'Bamboo Pavilion',
            'description' => 'Paviliun privat dengan pemandangan taman bambu dan dua bed treatment.',
            'translations' => [
                'en' => [
                    'name' => 'Bamboo Pavilion',
                    'description' => 'A private pavilion overlooking the bamboo garden, with two treatment beds.',
                ],
                'ja' => [
                    'name' => 'バンブーパビリオン',
                    'description' => '竹の庭を望むプライベートパビリオン。施術ベッドを2台ご用意。',
                ],
            ],
            'room_type' => 'garden',
            'min_guests' => 1,
            'max_guests' => 2,
            'features' => ['garden_view', 'couple_beds', 'private_shower', 'quiet'],
            'reservation_fee' => 150000,
            'minimum_spend' => null,
        ]];
        foreach ($items as $index => $item) {
            TreatmentRoom::updateOrCreate(['spa_id' => $spa->id, 'slug' => $item['slug']], [...$item, 'is_active' => true, 'sort_order' => $index]);
        }
    }
}
