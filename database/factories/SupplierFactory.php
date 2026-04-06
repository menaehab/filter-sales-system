<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
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
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Supplier $supplier): void {
            $supplier->syncPhones([
                ['number' => '01'.$this->faker->randomElement(['0', '1', '2', '5']).$this->faker->numerify('########')],
            ]);
        });
    }
}
