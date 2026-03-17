<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sell_price' => $this->faker->randomFloat(2, 10, 500),
            'cost_price' => $this->faker->randomFloat(2, 5, 300),
            'quantity' => $this->faker->numberBetween(1, 10),
            'sale_id' => \App\Models\Sale::factory(),
            'product_id' => \App\Models\Product::factory(),
        ];
    }
}
