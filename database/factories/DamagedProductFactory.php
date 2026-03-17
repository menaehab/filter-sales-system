<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DamagedProduct>
 */
class DamagedProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_price' => $this->faker->randomFloat(2, 10, 500),
            'quantity' => $this->faker->numberBetween(1, 10),
            'reason' => $this->faker->sentence(),
            'product_id' => \App\Models\Product::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
