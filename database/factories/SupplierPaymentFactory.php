<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierPayment>
 */
class SupplierPaymentFactory extends Factory
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
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'check']),
            'note' => $this->faker->optional(0.5)->sentence(),
            'supplier_id' => \App\Models\Supplier::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
