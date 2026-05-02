<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WaterFilter;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    actAsAdmin($this);
});

it('syncs installment start dates from the customer filter installation date', function () {
    $customer = Customer::factory()->create(['name' => 'Sync Customer']);
    $product = Product::factory()->create([
        'quantity' => 10,
    ]);

    $filter = WaterFilter::create([
        'filter_model' => 'Sync Filter',
        'address' => 'Sync Address',
        'installed_at' => '2026-02-14',
        'is_installed' => true,
        'customer_id' => $customer->id,
    ]);

    $sale = Sale::create([
        'dealer_name' => 'Sync Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 30,
        'installment_months' => 3,
        'installment_start_date' => null,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    Artisan::call('sales:sync-installment-start');

    expect(Artisan::output())->toContain('Updated 1 sale(s).');

    $sale->refresh();

    expect($sale->installment_start_date?->toDateString())->toBe('2026-02-14');
});

it('keeps sales unchanged during a dry run', function () {
    $customer = Customer::factory()->create(['name' => 'Dry Run Customer']);
    $product = Product::factory()->create([
        'quantity' => 10,
    ]);

    $filter = WaterFilter::create([
        'filter_model' => 'Dry Run Filter',
        'address' => 'Dry Run Address',
        'installed_at' => '2026-03-10',
        'is_installed' => true,
        'customer_id' => $customer->id,
    ]);

    $sale = Sale::create([
        'dealer_name' => 'Dry Run Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 30,
        'installment_months' => 3,
        'installment_start_date' => null,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    Artisan::call('sales:sync-installment-start', ['--dry-run' => true]);

    expect(Artisan::output())->toContain('[DRY]');

    $sale->refresh();

    expect($sale->installment_start_date)->toBeNull();
});
