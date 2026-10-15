<?php

namespace App\Modules\Reservation\Queries;

use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\RoomAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * No table is free in the requested area and slot: find the other areas that
 * can seat the same party at the same time, and the other slots of the same
 * area that day.
 */
class FindAlternatives
{
    public function __construct(private readonly RoomAvailability $availability) {}

    /**
     * @return Collection<int, array{type: string, slug?: string, time_slot?: string, name: string, fee?: float}>
     */
    public function handle(Spa $spa, TreatmentRoom $requested, CarbonImmutable $date, string $timeSlot, int $guests, string $locale): Collection
    {
        $otherAreas = $spa->treatmentRooms()
            ->where('is_active', true)
            ->whereKeyNot($requested->id)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (TreatmentRoom $area) => $this->availability->fitsParty($area, $guests))
            ->map(function (TreatmentRoom $area) use ($date, $timeSlot, $locale) {
                $quote = $this->availability->quote($area, $date, $timeSlot);

                return $quote ? [
                    'type' => 'area',
                    'slug' => $area->slug,
                    'name' => $area->translatedName($locale),
                    'fee' => $quote['fee'],
                ] : null;
            })
            ->filter()
            ->values();

        $otherSlots = collect($this->availability->availableSlots($requested, $date))
            ->reject(fn (string $slot) => $slot === $timeSlot)
            ->map(fn (string $slot) => [
                'type' => 'slot',
                'time_slot' => $slot,
                'name' => RoomAvailability::slotRange($slot),
            ])
            ->values();

        return $otherSlots->merge($otherAreas)->values();
    }
}
