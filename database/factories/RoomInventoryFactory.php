<?php

namespace Database\Factories;

use App\Models\RoomInventory;
use App\Models\TreatmentRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomInventory>
 */
class RoomInventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'treatment_room_id' => TreatmentRoom::factory(),
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'total_rooms' => 2,
            'reserved_rooms' => 0,
        ];
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => ['reserved_rooms' => $attributes['total_rooms']]);
    }
}
