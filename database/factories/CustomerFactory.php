<?php

namespace Database\Factories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'national_number' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'place_id' => Place::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Customer $customer): void {
            $customer->syncPhones([
                ['number' => '01'.$this->faker->randomElement(['0', '1', '2', '5']).$this->faker->numerify('########')],
            ]);
        });
    }
}
