<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\TreatmentRoom;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ReservationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Reservation\Support\RoomAvailability;

final class SearchRoomsTool implements Tool
{
    public function __construct(
        private readonly RoomAvailability $availability,
        private readonly ReservationInput $input,
    ) {}

    public function name(): string
    {
        return 'search_rooms';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Find treatment rooms that fit the guest\'s date and party size, with real-time room availability already checked. Optionally narrow to one time slot. Use this whenever a guest describes what they want (date, time, party size, indoor or outdoor) rather than naming one area.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Reservation date, YYYY-MM-DD'],
                        'guests' => ['type' => 'integer', 'minimum' => 1],
                        'time_slot' => ['type' => 'string', 'description' => 'Optional slot start time, HH:MM, one of the reservation slots'],
                        'room_type' => ['type' => 'string', 'enum' => TreatmentRoom::areaTypes(), 'description' => 'Optional preference'],
                    ],
                    'required' => ['date', 'guests'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $spa = $context->spa;
        $date = $this->input->parseDate($spa, (string) ($arguments['date'] ?? ''));

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($spa, $date)) {
            return new ToolResult($error);
        }

        $guests = (int) ($arguments['guests'] ?? 0);
        $slot = $arguments['time_slot'] ?? null;
        $areaType = $arguments['room_type'] ?? null;

        if ($slot && ! $this->input->isKnownSlot($spa, (string) $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($spa));
        }

        $matches = [];

        foreach ($spa->treatmentRooms()->where('is_active', true)->with('images')->orderBy('sort_order')->get() as $area) {
            if (! $this->availability->fitsParty($area, $guests) || ($areaType && $area->room_type !== $areaType)) {
                continue;
            }

            $slots = $this->availability->availableSlots($area, $date);

            if ($slot) {
                $slots = array_values(array_intersect($slots, [$slot]));
            }

            if ($slots === []) {
                continue;
            }

            $matches[] = [
                'treatment_room_slug' => $area->slug,
                ...CatalogSummaries::treatmentRoom($area, $spa, $context->locale),
                'date' => $date->toDateString(),
                'available_slots' => array_map(fn (string $s) => ['time_slot' => $s, 'time_range' => RoomAvailability::slotRange($s)], $slots),
            ];
        }

        if ($matches === []) {
            return new ToolResult('No treatment room has a available room for that date, party size and time. Tell the guest honestly and offer a different time or date, or call request_human_handover for a large group.');
        }

        return ToolResult::json($matches, [
            'type' => 'rooms_results',
            'date' => $date->toDateString(),
            'guests' => $guests,
            'areas' => $matches,
        ]);
    }
}
