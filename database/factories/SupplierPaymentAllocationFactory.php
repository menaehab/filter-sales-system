<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierPaymentAllocation>
 */
class SupplierPaymentAllocationFactory extends Factory
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
            'supplier_payment_id' => \App\Models\SupplierPayment::factory(),
            'purchase_id' => \App\Models\Purchase::factory(),
        ];
    }
}
