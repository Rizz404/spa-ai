<?php

namespace Database\Factories;

use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Rooms\Enums\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TreatmentRoom>
 */
class TreatmentRoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'spa_id' => Spa::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'room_type' => RoomType::Single->value,
            'min_guests' => 1,
            'max_guests' => 2,
            'features' => [],
            'reservation_fee' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forGuests(int $min, int $max): static
    {
        return $this->state(fn () => ['min_guests' => $min, 'max_guests' => $max]);
    }

    public function withFee(float $fee): static
    {
        return $this->state(fn () => ['reservation_fee' => $fee]);
    }
}
