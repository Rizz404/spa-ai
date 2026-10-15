<?php

namespace App\Modules\Reservation\Support;

use App\Models\RoomInventory;
use App\Models\TreatmentRoom;
use Carbon\CarbonImmutable;

final class OpenRoomInventory
{
    /** Open missing slots without changing existing capacity or reservations. */
    public function handle(TreatmentRoom $room): int
    {
        $spa = $room->spa;
        $today = CarbonImmutable::now($spa->timezone)->startOfDay();
        $rows = [];
        for ($day = 0; $day <= RoomAvailability::MAX_ADVANCE_DAYS; $day++) {
            foreach (config('spa.booking_slots') as $slot) {
                $start = CarbonImmutable::parse($today->addDays($day)->toDateString().' '.$slot, $spa->timezone);
                $open = CarbonImmutable::parse($start->toDateString().' '.$spa->opening_time, $spa->timezone);
                $close = CarbonImmutable::parse($start->toDateString().' '.$spa->closing_time, $spa->timezone);
                if ($start->lessThan($open) || $start->addMinutes(RoomAvailability::SLOT_MINUTES)->greaterThan($close)) {
                    continue;
                }
                $rows[] = [
                    'treatment_room_id' => $room->id,
                    'reservation_date' => $start->toDateString(),
                    'time_slot' => $slot,
                    'total_rooms' => 1,
                    'reserved_rooms' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        $inserted = 0;
        foreach (array_chunk($rows, 100) as $chunk) {
            $inserted += RoomInventory::query()->insertOrIgnore($chunk);
        }

        return $inserted;
    }
}
