<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ReservationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Reservation\Support\RoomAvailability;

final class CheckRoomAvailabilityTool implements Tool
{
    public function __construct(
        private readonly RoomAvailability $availability,
        private readonly ReservationInput $input,
    ) {}

    public function name(): string
    {
        return 'check_room_availability';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the current, real-time availability and exact reservation fee for one treatment room on a specific date and time slot. ALWAYS call this before telling a guest a room is available or confirming a fee — never state availability from memory.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'treatment_room_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'guests' => ['type' => 'integer', 'minimum' => 1],
                    ],
                    'required' => ['treatment_room_slug', 'date', 'time_slot'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $spa = $context->spa;
        $area = CatalogSummaries::treatmentRoomBySlug($spa, (string) ($arguments['treatment_room_slug'] ?? ''));

        if (! $area) {
            return new ToolResult('Rooms area not found.');
        }

        $date = $this->input->parseDate($spa, (string) ($arguments['date'] ?? ''));
        $slot = (string) ($arguments['time_slot'] ?? '');

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($spa, $date)) {
            return new ToolResult($error);
        }

        if (! $this->input->isKnownSlot($spa, $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($spa));
        }

        $guests = isset($arguments['guests']) ? (int) $arguments['guests'] : null;

        if ($guests !== null && ! $this->availability->fitsParty($area, $guests)) {
            return new ToolResult("This party size does not suit that treatment room (it seats {$area->min_guests}-{$area->max_guests} guests). Suggest another treatment room, or call request_human_handover for a large group.");
        }

        $quote = $this->availability->isSlotInPast($spa, $date, $slot)
            ? null
            : $this->availability->quote($area, $date, $slot);

        if (! $quote) {
            $alternatives = array_map(
                fn (string $s) => ['time_slot' => $s, 'time_range' => RoomAvailability::slotRange($s)],
                $this->availability->availableSlots($area, $date),
            );

            return ToolResult::json([
                'available' => false,
                'treatment_room_slug' => $area->slug,
                'message' => 'No available room in this treatment room for that slot.',
                'other_available_slots_that_day' => $alternatives,
            ], ['type' => 'availability', 'available' => false, 'treatment_room_slug' => $area->slug]);
        }

        $result = [
            'available' => true,
            'treatment_room_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_rooms' => $quote['available_rooms'],
            'reservation_fee' => $quote['fee'],
            'minimum_spend' => $quote['minimum_spend'],
            'currency' => $spa->currency,
        ];

        return ToolResult::json($result, ['type' => 'availability', 'available' => true, 'quote' => $result]);
    }
}
