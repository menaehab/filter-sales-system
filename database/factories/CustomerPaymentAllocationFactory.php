<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerPaymentAllocation>
 */
class CustomerPaymentAllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'customer_payment_id' => \App\Models\CustomerPayment::factory(),
            'sale_id' => \App\Models\Sale::factory(),
        ];
    }
}
