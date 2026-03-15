<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'S-' . $this->faker->unique()->randomNumber(6, true),
            'dealer_name' => $this->faker->company(),
            'user_name' => $this->faker->name(),
            'total_price' => $this->faker->randomFloat(2, 50, 1000),
            'payment_type' => $this->faker->randomElement(['cash', 'installment']),
            'installment_amount' => null,
            'installment_months' => null,
            'user_id' => \App\Models\User::factory(),
            'customer_id' => \App\Models\Customer::factory(),
        ];
    }
}
