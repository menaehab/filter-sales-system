<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_name' => $this->faker->company(),
            'user_name' => $this->faker->name(),
            'total_price' => $this->faker->randomFloat(2, 50, 1000),
            'payment_type' => 'cash',
            'installment_amount' => null,
            'installment_months' => null,
            'user_id' => \App\Models\User::factory(),
            'supplier_id' => \App\Models\Supplier::factory(),
        ];
    }

    public function cash(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_type' => 'cash',
                'installment_months' => null,
                'installment_amount' => null,
            ];
        });
    }

    public function installment(): self
    {
        return $this->state(function (array $attributes) {
            $months = $this->faker->numberBetween(2, 12);
            return [
                'payment_type' => 'installment',
                'installment_months' => $months,
                'installment_amount' => $attributes['total_price'] / $months,
            ];
        });
    }
}
