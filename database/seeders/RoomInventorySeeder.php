<?php

namespace Database\Seeders;

use App\Models\Spa;
use App\Modules\Reservation\Support\OpenRoomInventory;
use Illuminate\Database\Seeder;

class RoomInventorySeeder extends Seeder
{
    public function run(OpenRoomInventory $open): void
    {
        $spa = Spa::where('slug', 'spa-ai')->firstOrFail();
        foreach ($spa->treatmentRooms()->where('is_active', true)->get() as $room) {
            $open->handle($room);
        }
    }
}
