<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Reservation;
use App\Models\RoomInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeReservationStatus
{
    public function handle(Reservation $reservation, string $status): Reservation
    {
        return DB::transaction(function () use ($reservation, $status) {
            $current = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($current->status === $status) {
                return $current;
            }
            if ($current->status === Reservation::STATUS_CANCELLED || ! in_array($status, [Reservation::STATUS_CONFIRMED, Reservation::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages(['status' => 'A cancelled booking cannot be reopened. Create a new request to check capacity again.']);
            }
            if ($status === Reservation::STATUS_CANCELLED) {
                RoomInventory::where('treatment_room_id', $current->treatment_room_id)
                    ->whereDate('reservation_date', $current->reservation_date->toDateString())
                    ->where('time_slot', $current->time_slot)
                    ->where('reserved_rooms', '>', 0)
                    ->decrement('reserved_rooms');
            }
            $current->update(['status' => $status]);

            return $current;
        });
    }

    public function releaseRoom(Reservation $reservation): void
    {
        $this->handle($reservation, Reservation::STATUS_CANCELLED);
    }
}
