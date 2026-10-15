<?php

namespace Database\Factories;

use App\Models\RoomImage;
use App\Models\TreatmentRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomImage>
 */
class RoomImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'treatment_room_id' => TreatmentRoom::factory(),
            'image_url' => 'images/spa/room.png',
            'alt_text' => fake()->sentence(3),
            'tags' => [],
            'sort_order' => 0,
        ];
    }
}
