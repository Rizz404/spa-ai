<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Spa;
use App\Models\Treatment;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Reservation::generateReference(),
            'spa_id' => Spa::factory(),
            'treatment_room_id' => fn (array $attributes) => TreatmentRoom::factory()->create(['spa_id' => $attributes['spa_id']])->id,
            'treatment_id' => fn (array $attributes) => Treatment::factory()->create(['spa_id' => $attributes['spa_id']])->id,
            'treatment_name' => fn (array $attributes) => Treatment::findOrFail($attributes['treatment_id'])->name,
            'treatment_price' => fn (array $attributes) => Treatment::findOrFail($attributes['treatment_id'])->price,
            'duration_minutes' => 60,
            'guest_name' => fake()->name(),
            'guest_phone' => '+62 811 1111 222',
            'contact_type' => 'phone',
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'guests' => 2,
            'quoted_total' => 0,
            'status' => ReservationStatus::Pending->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Confirmed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Cancelled->value]);
    }
}
