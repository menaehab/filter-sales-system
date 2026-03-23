<?php

namespace Database\Factories;

use App\Enums\WaterQualityTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaterReading>
 */
class WaterReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'technician_name' => $this->faker->name(),
            'tds' => $this->faker->randomFloat(2, 0, 1000),
            'water_quality' => $this->faker->randomElement(WaterQualityTypeEnum::values()),
            'water_filter_id' => \App\Models\WaterFilter::factory(),
        ];
    }
}
