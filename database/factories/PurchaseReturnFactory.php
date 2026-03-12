<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseReturn>
 */
class PurchaseReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'reason' => $this->faker->sentence(),
            'cash_refund' => $this->faker->boolean(),
            'purchase_id' => \App\Models\Purchase::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
