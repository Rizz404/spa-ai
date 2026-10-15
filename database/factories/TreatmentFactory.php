<?php

namespace Database\Factories;

use App\Models\Spa;
use App\Models\Treatment;
use App\Modules\Catalog\Enums\TreatmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Treatment>
 */
class TreatmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'spa_id' => Spa::factory(),
            'category' => fake()->randomElement(TreatmentCategory::values()),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(25, 75) * 10000,
            'duration_minutes' => 60,
            'tags' => [],
            'contraindications' => [],
            'is_featured' => false,
            'is_unavailable' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn () => ['is_unavailable' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
