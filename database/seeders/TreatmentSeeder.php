<?php

namespace Database\Seeders;

use App\Models\Spa;
use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $spa = Spa::where('slug', 'spa-ai')->firstOrFail();
        $items = [[
            'slug' => 'botanical-calm',
            'category' => 'massage',
            'name' => 'Botanical Calm Massage',
            'description' => 'Pijat lembut menggunakan minyak botanikal, untuk jeda yang menenangkan.',
            'translations' => [
                'en' => [
                    'name' => 'Botanical Calm Massage',
                    'description' => 'A gentle botanical oil massage for a calm, unhurried pause.',
                ],
                'ja' => [
                    'name' => 'ボタニカルカームマッサージ',
                    'description' => '植物由来のオイルを使った穏やかなマッサージで、ゆっくりとしたひとときを。',
                ],
            ],
            'duration_minutes' => 60,
            'duration_label' => null,
            'price' => 350000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['relaxation', 'botanical', 'signature'],
            'contraindications' => ['pregnancy', 'recent_surgery'],
            'is_featured' => true,
            'is_unavailable' => false,
        ], [
            'slug' => 'deep-release',
            'category' => 'massage',
            'name' => 'Deep Release Massage',
            'description' => 'Ritual pijat dengan tekanan lebih kuat yang disesuaikan bersama terapis.',
            'translations' => [
                'en' => [
                    'name' => 'Deep Release Massage',
                    'description' => 'A firmer-pressure massage ritual, tailored in consultation with your therapist.',
                ],
                'ja' => [
                    'name' => 'ディープリリースマッサージ',
                    'description' => 'セラピストと相談しながら強さを調整する、しっかりとしたマッサージ。',
                ],
            ],
            'duration_minutes' => 90,
            'duration_label' => null,
            'price' => 480000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['relaxation', 'bestseller'],
            'contraindications' => ['pregnancy', 'recent_surgery'],
            'is_featured' => false,
            'is_unavailable' => false,
        ], [
            'slug' => 'petal-body-polish',
            'category' => 'body',
            'name' => 'Petal Body Polish',
            'description' => 'Eksfoliasi tubuh lembut dengan aroma bunga dan sentuhan pelembap.',
            'translations' => [
                'en' => [
                    'name' => 'Petal Body Polish',
                    'description' => 'A gentle body exfoliation with floral notes and a moisturising finish.',
                ],
                'ja' => [
                    'name' => 'ペタルボディポリッシュ',
                    'description' => '花の香りを楽しみながら、やさしく角質をケアし保湿で仕上げます。',
                ],
            ],
            'duration_minutes' => 60,
            'duration_label' => null,
            'price' => 380000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['botanical', 'gentle'],
            'contraindications' => ['sensitive_skin', 'recent_surgery'],
            'is_featured' => false,
            'is_unavailable' => false,
        ], [
            'slug' => 'dew-facial',
            'category' => 'facial',
            'name' => 'Dew Facial Ritual',
            'description' => 'Rangkaian pembersihan, masker, dan pijat wajah yang lembut.',
            'translations' => [
                'en' => [
                    'name' => 'Dew Facial Ritual',
                    'description' => 'A gentle sequence of cleansing, a face mask and facial massage.',
                ],
                'ja' => [
                    'name' => 'デューフェイシャルリチュアル',
                    'description' => 'クレンジング、マスク、フェイシャルマッサージを組み合わせたやさしいケア。',
                ],
            ],
            'duration_minutes' => 60,
            'duration_label' => null,
            'price' => 420000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['gentle', 'botanical'],
            'contraindications' => ['sensitive_skin'],
            'is_featured' => false,
            'is_unavailable' => false,
        ], [
            'slug' => 'warm-stone-ritual',
            'category' => 'ritual',
            'name' => 'Warm Stone Ritual',
            'description' => 'Ritual batu hangat dan minyak aromatik dengan konsultasi suhu sebelum sesi.',
            'translations' => [
                'en' => [
                    'name' => 'Warm Stone Ritual',
                    'description' => 'Warm stones and aromatic oils, with temperature preferences discussed before your session.',
                ],
                'ja' => [
                    'name' => 'ウォームストーンリチュアル',
                    'description' => '温めた石とアロマオイルを使用。施術前にお好みの温度を確認します。',
                ],
            ],
            'duration_minutes' => 90,
            'duration_label' => null,
            'price' => 520000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['relaxation', 'signature'],
            'contraindications' => ['heat_sensitivity', 'pregnancy'],
            'is_featured' => false,
            'is_unavailable' => false,
        ], [
            'slug' => 'sanctuary-journey',
            'category' => 'package',
            'name' => 'Sanctuary Journey',
            'description' => 'Paket pijat botanikal dan perawatan tubuh dalam satu ritual dua jam.',
            'translations' => [
                'en' => [
                    'name' => 'Sanctuary Journey',
                    'description' => 'Botanical massage and body care in one considered two-hour ritual.',
                ],
                'ja' => [
                    'name' => 'サンクチュアリジャーニー',
                    'description' => 'ボタニカルマッサージとボディケアを組み合わせた2時間のリチュアル。',
                ],
            ],
            'duration_minutes' => 120,
            'duration_label' => null,
            'price' => 720000,
            'image_url' => 'images/spa/treatment.png',
            'tags' => ['relaxation', 'botanical', 'signature'],
            'contraindications' => ['pregnancy', 'sensitive_skin', 'recent_surgery'],
            'is_featured' => true,
            'is_unavailable' => false,
        ]];
        foreach ($items as $index => $item) {
            Treatment::updateOrCreate(['spa_id' => $spa->id, 'slug' => $item['slug']], [...$item, 'is_active' => true, 'sort_order' => $index]);
        }
    }
}
