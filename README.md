# Spa AI

Spa multi-tenant dengan AI Spa Concierge, katalog treatment/paket, ruang perawatan, dan booking yang dikonfirmasi staf. Antarmuka Zen natural memakai sage, ivory, navigasi kiri, pemandu kanan, panel tengah, dan tiga bahasa (id/en/ja).

Produksi: https://spa-ai.fts-tech.co.id/ · Sanctuary: https://spa-ai.fts-tech.co.id/spa-ai · Admin: https://spa-ai.fts-tech.co.id/admin. Dikelola Laravel Toolkit Plesk dari branch `main`, dengan PostgreSQL, HTTPS, dan inventori bergulir. Konten awal adalah demo; tinjau alamat, harga, dan kebijakan sebelum dipakai untuk bisnis nyata.

## Menjalankan lokal

Prasyarat: PHP 8.4+, SQLite, Composer, Node >=22.12 (disarankan Node 24).

```bash
composer setup
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8015
```

- Pembuka: http://127.0.0.1:8015/
- Sanctuary: http://127.0.0.1:8015/spa-ai
- Admin: http://127.0.0.1:8015/admin

Login demo lokal: `owner@spaai.test` / `password`. Jangan gunakan seeder akun demo di produksi.

Demo berisi enam treatment, tiga ruang, delapan knowledge items, dan inventori hingga 60 hari ke depan. Alamat, email, harga, fasilitas, serta kebijakan adalah contoh yang perlu ditinjau pemilik sebelum produksi.

## Booking

Wizard: tanggal/sesi → treatment/jumlah tamu → ruang → kontak → ringkasan. Mendukung satu atau dua tamu; pasangan menerima treatment yang sama secara bersamaan. Durasi layanan 15–120 menit, terpisah dari alokasi ruang dua jam.

Estimasi total = harga treatment per tamu × jumlah tamu + biaya ruang. Pengiriman membuat status `pending`, mengunci kapasitas, dan menyimpan salinan nama/harga/durasi treatment. Tidak ada pembayaran online. Staf dapat mengonfirmasi atau membatalkan. Pembatalan mengembalikan kapasitas sekali; booking yang dibatalkan tidak dapat dibuka kembali.

Draft bertahan saat berpindah halaman dan bahasa. Data kontak tidak dikirim sebagai konteks UI ke model.

## AI dan suara

Isi secara aman di `.env`:

```dotenv
LOCAL_LLM_BASE_URL=
LOCAL_LLM_API_KEY=
LOCAL_LLM_MODEL=
ASSISTANT_REASONING_EFFORT=none
OPENROUTER_API_KEY=
OPENROUTER_TTS_MODEL=
OPENROUTER_TTS_VOICE=
```

Base URL tanpa `/v1`; model perlu mendukung function calling. TTS opsional. Tanpa kredensial, UI dan booking tersedia tetapi jawaban LLM nyata belum aktif.

Tool: search_knowledge, search_treatments, get_treatment, search_rooms, check_room_availability, create_reservation_request, request_human_handover. Tenant dan percakapan diambil dari konteks server. Harga, durasi, dan kapasitas harus berasal dari tool. Kesesuaian layanan untuk kondisi kesehatan diserahkan ke staf, bukan diputuskan AI.

CLI chat interaktif: `php artisan concierge:chat spa-ai` (mengirim prompt nyata).

## Persiapan produksi Plesk

Lokal/CI memakai SQLite; `deploy/env.production.example` memakai PostgreSQL 13+. Gunakan PHP 8.4, Node 24, document root `public`, database/user khusus, HTTPS, dan cookie session aman. Pertahankan APP_KEY dari Laravel Toolkit, jangan menyalin key lokal.

TRUSTED_PROXIES dibaca aplikasi. Isi hanya IP/CIDR reverse proxy yang digunakan. PHP: memory_limit 256M, max_execution_time 95; Apache vhost target: ProxyTimeout 100. Biarkan opcache.validate_timestamps=1 untuk deployment Toolkit.

`deploy/plesk-actions.sh.example` memuat build, migrate, dan optimize untuk Additional Deployment Actions. Example nginx/supervisor hanya referensi VPS dan tidak dipasang ke Plesk.

```bash
php artisan db:seed --class=ProductionSpaSeeder --force --no-interaction
php artisan spa:create-admin --email=admin@example.com --name=Admin --spa=spa-ai
```

Command admin meminta password tersembunyi dua kali (minimum 12 karakter), memeriksa email unik, dan menautkan owner aktif. Password bukan argumen CLI. Seeder produksi membuat tenant kosong tanpa user. Profil tenant perlu diisi dengan alamat, kontak dan jam operasional sebenarnya sebelum go-live.

Untuk data demo yang sudah ditinjau, seed per kelas: SpaSeeder, TreatmentSeeder, TreatmentRoomSeeder, RoomImageSeeder, RoomInventorySeeder, SpaKnowledgeItemSeeder. Jangan menjalankan UserSeeder/SpaUserSeeder di produksi.

Ruang aktif yang baru langsung membuka kapasitas. Scheduler harian pukul 01:00 Asia/Jakarta menambah slot yang belum ada hingga 60 hari tanpa menimpa kapasitas/booking yang tersimpan. Aktifkan scheduler melalui Laravel Toolkit.

```bash
php artisan spa:open-inventory --spa=spa-ai
php artisan schedule:list
```

Health check: `/up`. Repository publik: https://github.com/Rizz404/spa-ai, branch `main`. LLM dan TTS dikonfigurasi melalui environment privat server. Endpoint model LLM telah merespons 200 dan memuat model terpilih; chat dan audio nyata belum diuji. SMTP belum diaktifkan (`MAIL_MAILER=log`).

## Pemeriksaan

`npm run build`, `npm run lint:ui`, `php artisan view:cache`, dan Pint digunakan untuk pemeriksaan statis/build. Browser QA mencakup desktop 1440×900 dan mobile 390×844, navigasi scene, bahasa, chat, pengiriman booking, login admin, konfirmasi dan pembatalan. Acuan Cafe AI dijalankan dari salinan terpisah; folder sumber tidak diubah.

CI GitHub pada branch `main` telah lulus: 111 tes PHP (496 assertions), 16 tes JavaScript, Pint, UI conventions, build, Composer audit, dan npm audit runtime. CI membangun aset Vite sebelum tes halaman. Provider pada tes chat menggunakan HTTP fake; hasil tes tidak membuktikan jawaban LLM/TTS nyata.

## Aset dan prompt imagegen

Aset final berada di `public/images/spa/`, dibuat dengan tool imagegen bawaan, bukan CLI. Concierge mempertahankan alpha transparan. Prompt set:

- `sanctuary.png`: A refined tranquil Indonesian Zen spa reception opening into a garden treatment sanctuary, sage limewash walls, ivory stone floors, pale oak, arched doorway on right framing bamboo and a gentle waterfall, linen treatment bed partly visible at right, ceramic bowl, folded towels, delicate tropical foliage. Hand-painted editorial illustration with fine architectural detail and soft light, mature calm aesthetic, muted sage and warm ivory palette. Broad softly lit uncluttered middle-left wall for website copy overlays. Full bleed, eye level, layered spatial depth, no people, no typography, no logos, no borders.
- `concierge.png`: One friendly adult Indonesian female spa concierge, elegant sage wrap tunic with ivory piping, neatly tied dark hair, warm welcoming expression, open palm gesture at waist, standing upright facing viewer with slight three-quarter turn to left. Tasteful hand-painted editorial illustration, natural proportions, soft watercolor gouache detail, warm ivory and sage palette. Portrait composition from head to mid-thigh, clean flat horizontal crop at lower edge; entire head and hands inside frame. Only the single character, genuinely transparent background, no scenery, no shadow backdrop, no text or logo.
- `treatment.png`: A close-up spa still life on ivory travertine: amber botanical oil bottle, frangipani flower, folded sage linen, smooth warm basalt stones, ceramic bowl. Editorial hand-painted illustration with realistic texture, warm daylight, muted sage and ivory palette, wide 3:2 landscape. No people, no words, no brands, no borders.
- `room.png`: A serene private Zen spa treatment room with one linen treatment bed, sage plaster, pale oak screen, fresh white frangipani, folded towels, bamboo courtyard seen through arched window. Refined hand-painted architectural editorial illustration, warm daylight, muted sage and ivory, wide landscape. No people, no text or logos.
- `garden.png`: A quiet Indonesian spa relaxation garden: two pale oak lounge chairs beside a shallow reflecting pool, bamboo, stepping stones, soft linen canopy, stone teapot with herbal tea in foreground. Refined hand-painted editorial illustration, warm dappled light, sage and ivory palette, wide landscape. No people, no words, no logo.
