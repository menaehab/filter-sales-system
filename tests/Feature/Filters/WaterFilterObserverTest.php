<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WaterFilter;

beforeEach(function () {
    actAsAdmin($this);
});

it('backfills installment sales when a filter installation date is set', function () {
    $customer = Customer::factory()->create(['name' => 'Observer Customer']);
    $product = Product::factory()->create([
        'quantity' => 10,
    ]);

    $filter = WaterFilter::create([
        'filter_model' => 'Observer Filter',
        'address' => 'Observer Address',
        'customer_id' => $customer->id,
        'is_installed' => false,
        'installed_at' => null,
    ]);

    $missingStartSale = Sale::create([
        'dealer_name' => 'Observer Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 30,
        'installment_months' => 3,
        'installment_start_date' => null,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => '2026-03-01 08:00:00',
        'updated_at' => '2026-03-01 08:00:00',
    ]);

    SaleItem::create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 2,
        'sale_id' => $missingStartSale->id,
        'product_id' => $product->id,
    ]);

    $placeholderStartSale = Sale::create([
        'dealer_name' => 'Observer Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 120,
        'payment_type' => 'installment',
        'installment_amount' => 40,
        'installment_months' => 3,
        'installment_start_date' => '2026-03-01',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => '2026-03-01 12:00:00',
        'updated_at' => '2026-03-01 12:00:00',
    ]);

    SaleItem::create([
        'sell_price' => 60,
        'cost_price' => 45,
        'quantity' => 2,
        'sale_id' => $placeholderStartSale->id,
        'product_id' => $product->id,
    ]);

    $manualStartSale = Sale::create([
        'dealer_name' => 'Observer Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 150,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 3,
        'installment_start_date' => '2026-01-15',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => '2026-03-01 15:00:00',
        'updated_at' => '2026-03-01 15:00:00',
    ]);

    SaleItem::create([
        'sell_price' => 75,
        'cost_price' => 55,
        'quantity' => 2,
        'sale_id' => $manualStartSale->id,
        'product_id' => $product->id,
    ]);

    $filter->update([
        'is_installed' => true,
        'installed_at' => '2026-04-10',
    ]);

    $missingStartSale->refresh();
    $placeholderStartSale->refresh();
    $manualStartSale->refresh();

    expect($missingStartSale->installment_start_date?->toDateString())->toBe('2026-04-10');
    expect($placeholderStartSale->installment_start_date?->toDateString())->toBe('2026-04-10');
    expect($manualStartSale->installment_start_date?->toDateString())->toBe('2026-01-15');
});
