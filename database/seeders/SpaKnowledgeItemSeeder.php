<?php

namespace Database\Seeders;

use App\Models\Spa;
use App\Models\SpaKnowledgeItem;
use Illuminate\Database\Seeder;

class SpaKnowledgeItemSeeder extends Seeder
{
    public function run(): void
    {
        $spa = Spa::where('slug', 'spa-ai')->firstOrFail();
        $items = [[
            'category' => 'facilities',
            'title' => 'Taman relaksasi',
            'body' => 'Nikmati jeda sebelum atau setelah treatment di taman tenang kami.',
            'translations' => [
                'en' => [
                    'title' => 'Relaxation garden',
                    'body' => 'Take a quiet pause in our garden before or after your treatment.',
                ],
                'ja' => [
                    'title' => 'リラクゼーションガーデン',
                    'body' => '施術の前後に、静かな庭でひと息つけます。',
                ],
            ],
            'tags' => ['garden_view', 'relaxation'],
            'image_url' => 'images/spa/garden.png',
        ], [
            'category' => 'facilities',
            'title' => 'Shower & linen privat',
            'body' => 'Setiap ruang memiliki shower privat, handuk bersih, dan linen yang diganti setiap sesi.',
            'translations' => [
                'en' => [
                    'title' => 'Private shower & linen',
                    'body' => 'Each room has a private shower, fresh towels and linen changed between sessions.',
                ],
                'ja' => [
                    'title' => '専用シャワーとリネン',
                    'body' => '各部屋に専用シャワーと清潔なタオルを完備。リネンは毎回交換します。',
                ],
            ],
            'tags' => ['private_shower'],
            'image_url' => 'images/spa/room.png',
        ], [
            'category' => 'facilities',
            'title' => 'Ruang hening',
            'body' => 'Area istirahat bernuansa sage untuk menikmati suasana tenang. Mohon gunakan mode senyap pada ponsel.',
            'translations' => [
                'en' => [
                    'title' => 'Quiet lounge',
                    'body' => 'A sage-toned lounge for a quiet moment. Please keep your phone on silent.',
                ],
                'ja' => [
                    'title' => '静かなラウンジ',
                    'body' => 'セージ色のラウンジでくつろぎの時間を。携帯電話はマナーモードにお願いします。',
                ],
            ],
            'tags' => ['quiet'],
            'image_url' => 'images/spa/sanctuary.png',
        ], [
            'category' => 'general',
            'title' => 'Tentang Spa AI',
            'body' => 'Ini adalah konten demo Spa AI. Harga, fasilitas, dan kebijakan merupakan contoh untuk ditinjau pemilik sebelum digunakan di produksi.',
            'translations' => [
                'en' => [
                    'title' => 'About Spa AI',
                    'body' => 'This is Spa AI demo content. Prices, facilities and policies are examples for the owner to review before production.',
                ],
                'ja' => [
                    'title' => 'Spa AIについて',
                    'body' => 'Spa AIのデモコンテンツです。料金、設備、利用規約は本番運用前にオーナーが確認するためのサンプルです。',
                ],
            ],
            'tags' => ['demo'],
            'image_url' => null,
        ], [
            'category' => 'policies',
            'title' => 'Booking & pembayaran',
            'body' => 'Booking online adalah permintaan dan menunggu konfirmasi tim. Tidak ada pembayaran online saat pengiriman. Harga per tamu ditambah biaya ruang yang ditampilkan pada ringkasan.',
            'translations' => [
                'en' => [
                    'title' => 'Bookings & payment',
                    'body' => 'Online bookings are requests awaiting staff confirmation. No online payment is taken on submission. Estimates include the per-guest price plus the room supplement shown in your summary.',
                ],
                'ja' => [
                    'title' => 'ご予約とお支払い',
                    'body' => 'オンライン予約はリクエストです。スタッフの確認後に確定します。送信時に決済はありません。表示料金は人数分の施術料金とお部屋の追加料金を含みます。',
                ],
            ],
            'tags' => ['booking', 'payment'],
            'image_url' => null,
        ], [
            'category' => 'policies',
            'title' => 'Kedatangan & perubahan jadwal',
            'body' => 'Mohon datang 15 menit sebelum sesi. Untuk perubahan atau pembatalan, hubungi tim dengan nomor referensi booking.',
            'translations' => [
                'en' => [
                    'title' => 'Arrival & rescheduling',
                    'body' => 'Please arrive 15 minutes before your session. For changes or cancellation, contact the team with your booking reference.',
                ],
                'ja' => [
                    'title' => 'ご来店と日時の変更',
                    'body' => 'ご予約時間の15分前にお越しください。変更やキャンセルは受付番号を添えてスタッフにご連絡ください。',
                ],
            ],
            'tags' => ['arrival', 'cancellation'],
            'image_url' => null,
        ], [
            'category' => 'faq',
            'title' => 'Bagaimana memilih treatment?',
            'body' => 'Concierge dapat menjelaskan katalog dan durasi. Jika memiliki kondisi atau sensitivitas tertentu, konsultasikan kesesuaian dengan staf sebelum booking. Concierge tidak memberikan diagnosis atau nasihat medis.',
            'translations' => [
                'en' => [
                    'title' => 'How do I choose a treatment?',
                    'body' => 'The Concierge can explain the catalog and durations. Discuss conditions or sensitivities with staff before booking. The Concierge does not provide diagnoses or medical advice.',
                ],
                'ja' => [
                    'title' => '施術はどのように選べますか？',
                    'body' => 'コンシェルジュが施術内容と所要時間をご案内します。体調や敏感な部分については予約前にスタッフにご相談ください。診断や医療上の助言は行いません。',
                ],
            ],
            'tags' => ['suitability', 'sensitive_skin', 'pregnancy'],
            'image_url' => null,
        ], [
            'category' => 'faq',
            'title' => 'Berapa lama sesi saya?',
            'body' => 'Durasi treatment tercantum pada katalog. Ruang dialokasikan selama dua jam untuk satu permintaan. Untuk pasangan, treatment yang dipilih berlaku per tamu dan berlangsung bersamaan.',
            'translations' => [
                'en' => [
                    'title' => 'How long is my session?',
                    'body' => 'Treatment duration appears in the catalog. A room is allocated for a two-hour block per request. For couples, the selected treatment applies per guest and takes place at the same time.',
                ],
                'ja' => [
                    'title' => '施術時間はどのくらいですか？',
                    'body' => '所要時間はカタログに記載しています。お部屋は1回のリクエストにつき2時間確保します。ペアの場合、選択した施術をお一人ずつ同時に受けていただきます。',
                ],
            ],
            'tags' => ['duration', 'couple'],
            'image_url' => null,
        ]];
        foreach ($items as $index => $item) {
            SpaKnowledgeItem::updateOrCreate(['spa_id' => $spa->id, 'title' => $item['title']], [...$item, 'is_active' => true, 'sort_order' => $index]);
        }
    }
}
