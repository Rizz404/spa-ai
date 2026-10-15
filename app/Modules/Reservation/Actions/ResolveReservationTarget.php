<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\RoomAvailability;
use App\Modules\Reservation\Validation\ReservationRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Turns the guest's already well-formed request into the rooms area and
 * date it names, or tells them in their own language what does not fit.
 */
class ResolveReservationTarget
{
    public function __construct(private readonly RoomAvailability $availability) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: TreatmentRoom, 1: CarbonImmutable}
     *
     * @throws ValidationException
     */
    public function handle(Spa $spa, array $data, string $locale): array
    {
        $area = $spa->treatmentRooms()->where('is_active', true)->where('slug', $data['treatment_room_slug'])->first();

        if (! $area) {
            throw ValidationException::withMessages(['treatment_room_slug' => ReservationRules::message('area_unknown', $locale)]);
        }

        $treatment = $spa->treatments()->where('slug', $data['treatment_slug'])->where('is_active', true)->where('is_unavailable', false)->first();

        if (! $treatment || $treatment->duration_minutes < 15 || $treatment->duration_minutes > RoomAvailability::SLOT_MINUTES) {
            throw ValidationException::withMessages(['treatment_slug' => ReservationRules::message('treatment_unknown', $locale)]);
        }

        $date = CarbonImmutable::parse($data['reservation_date'], $spa->timezone)->startOfDay();
        $today = CarbonImmutable::now($spa->timezone)->startOfDay();

        if ($today->diffInDays($date) > RoomAvailability::MAX_ADVANCE_DAYS) {
            throw ValidationException::withMessages([
                'reservation_date' => ReservationRules::message('date_far', $locale, ['max' => (string) RoomAvailability::MAX_ADVANCE_DAYS]),
            ]);
        }

        if (! in_array($data['time_slot'], $this->availability->slotsFor($spa), true)) {
            throw ValidationException::withMessages(['time_slot' => ReservationRules::message('slot_unknown', $locale)]);
        }

        if ($this->availability->isSlotInPast($spa, $date, $data['time_slot'])) {
            throw ValidationException::withMessages(['time_slot' => ReservationRules::message('slot_past', $locale)]);
        }

        if (! $this->availability->fitsParty($area, (int) $data['guests'])) {
            throw ValidationException::withMessages(['guests' => ReservationRules::message('capacity', $locale)]);
        }

        return [$area, $date, $treatment];
    }
}
