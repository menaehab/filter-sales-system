<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleReturn>
 */
class SaleReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('#####'),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'reason' => $this->faker->sentence(),
            'cash_refund' => $this->faker->boolean(),
            'sale_id' => \App\Models\Sale::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
