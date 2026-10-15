<?php

namespace App\Modules\Reservation\Support;

use App\Models\RoomInventory;
use App\Models\Spa;
use App\Models\TreatmentRoom;
use Carbon\CarbonImmutable;

/**
 * The single place where table availability and slot quotes are computed.
 * Both the AI Spa Concierge tools and the guest-facing reservation wizard go
 * through here, so they can never disagree.
 */
class RoomAvailability
{
    /**
     * How far ahead a table can be requested.
     */
    public const MAX_ADVANCE_DAYS = 60;

    /**
     * Every slot is a fixed two-hour sitting.
     */
    public const SLOT_MINUTES = 120;

    /**
     * Whether the party size suits the rooms area.
     */
    public function fitsParty(TreatmentRoom $area, int $guests): bool
    {
        return $guests <= 2 && $guests >= max(1, $area->min_guests) && $guests <= $area->max_guests;
    }

    /**
     * The slot start times the spa takes reservations for, earliest first.
     *
     * @return list<string>
     */
    public function slotsFor(Spa $spa): array
    {
        return RoomInventory::query()
            ->whereIn('treatment_room_id', $spa->treatmentRooms()->where('is_active', true)->select('id'))
            ->distinct()
            ->orderBy('time_slot')
            ->pluck('time_slot')
            ->all();
    }

    /**
     * "11:00–13:00" for a slot that starts at 11:00.
     */
    public static function slotRange(string $slot): string
    {
        $start = CarbonImmutable::createFromFormat('H:i', $slot);

        return $start->format('H:i').'–'.$start->addMinutes(self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Whether the slot has already begun in the spa's own time zone.
     */
    public function isSlotInPast(Spa $spa, CarbonImmutable $date, string $slot): bool
    {
        $start = CarbonImmutable::parse($date->toDateString().' '.$slot, $spa->timezone);

        return $start->lessThanOrEqualTo(CarbonImmutable::now($spa->timezone));
    }

    /**
     * Checks one slot. Returns null if there is no available room in it, which is
     * the one place availability truth comes from, never the model.
     *
     * @return array{date: string, time_slot: string, time_range: string, available_rooms: int, fee: float, minimum_spend: ?float}|null
     */
    public function quote(TreatmentRoom $area, CarbonImmutable $date, string $slot, bool $lockForUpdate = false): ?array
    {
        $query = RoomInventory::where('treatment_room_id', $area->id)
            ->whereDate('reservation_date', $date->toDateString())
            ->where('time_slot', $slot);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (! $inventory || ! $inventory->isAvailable()) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'time_slot' => $slot,
            'time_range' => self::slotRange($slot),
            'available_rooms' => $inventory->availableRooms(),
            'fee' => (float) $area->reservation_fee,
            'minimum_spend' => $area->minimum_spend !== null ? (float) $area->minimum_spend : null,
        ];
    }

    /**
     * Every slot of the day that still has a available room and has not started.
     *
     * @return list<string>
     */
    public function availableSlots(TreatmentRoom $area, CarbonImmutable $date): array
    {
        $spa = $area->spa;

        return RoomInventory::where('treatment_room_id', $area->id)
            ->whereDate('reservation_date', $date->toDateString())
            ->orderBy('time_slot')
            ->get()
            ->filter(fn (RoomInventory $row) => $row->isAvailable() && ! $this->isSlotInPast($spa, $date, $row->time_slot))
            ->pluck('time_slot')
            ->values()
            ->all();
    }
}
