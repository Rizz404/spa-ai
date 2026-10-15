<?php

namespace Database\Seeders;

use App\Models\RoomImage;
use App\Models\Spa;
use Illuminate\Database\Seeder;

class RoomImageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Spa::where('slug', 'spa-ai')->firstOrFail()->treatmentRooms()->get() as $room) {
            RoomImage::updateOrCreate(['treatment_room_id' => $room->id, 'sort_order' => 0], [
                'image_url' => $room->room_type === 'garden' ? 'images/spa/garden.png' : 'images/spa/room.png',
                'alt_text' => $room->name.' — ilustrasi',
            ]);
        }
    }
}
