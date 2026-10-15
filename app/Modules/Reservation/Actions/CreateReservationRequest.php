<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Conversation;
use App\Models\Reservation;
use App\Models\RoomInventory;
use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\RoomAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Creates a pending reservation request and holds one table for it. Both the
 * reservation wizard and the AI Spa Concierge's reservation tool end up here.
 */
class CreateReservationRequest
{
    public function __construct(private readonly RoomAvailability $availability) {}

    /**
     * Returns null when no table is free in that slot any more.
     *
     * @param  array{reservation_date: CarbonImmutable, time_slot: string, guests: int, occasion?: ?string, guest_name: string, guest_email?: ?string, guest_phone?: ?string, contact_type?: ?string, notes?: ?string}  $data
     */
    public function handle(Spa $spa, TreatmentRoom $area, array $data, ?Conversation $conversation = null): ?Reservation
    {
        return DB::transaction(function () use ($spa, $area, $data, $conversation) {
            $treatment = $spa->treatments()->where('slug', $data['treatment_slug'])->where('is_active', true)->where('is_unavailable', false)->lockForUpdate()->first();

            if (! $treatment || $treatment->duration_minutes < 15 || $treatment->duration_minutes > RoomAvailability::SLOT_MINUTES || $area->spa_id !== $spa->id || ! $area->is_active || ! $this->availability->fitsParty($area, (int) $data['guests']) || $this->availability->isSlotInPast($spa, $data['reservation_date'], $data['time_slot'])) {
                return null;
            }

            $quote = $this->availability->quote($area, $data['reservation_date'], $data['time_slot'], lockForUpdate: true);

            if (! $quote) {
                return null;
            }

            $reservation = Reservation::create([
                'reference' => Reservation::generateReference(),
                'spa_id' => $spa->id,
                'treatment_room_id' => $area->id,
                'treatment_id' => $treatment->id,
                'treatment_name' => $treatment->name,
                'duration_minutes' => $treatment->duration_minutes,
                'treatment_price' => $treatment->price,
                'conversation_id' => $conversation?->id,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'contact_type' => $data['contact_type'] ?? null,
                'reservation_date' => $data['reservation_date']->toDateString(),
                'time_slot' => $data['time_slot'],
                'guests' => $data['guests'],
                'occasion' => $data['occasion'] ?? null,
                'quoted_total' => $quote['fee'] + (float) $treatment->price * (int) $data['guests'],
                'status' => Reservation::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            RoomInventory::where('treatment_room_id', $reservation->treatment_room_id)
                ->whereDate('reservation_date', $reservation->reservation_date->toDateString())
                ->where('time_slot', $reservation->time_slot)
                ->increment('reserved_rooms');

            return $reservation;
        });
    }
}
