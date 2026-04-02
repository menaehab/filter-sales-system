<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

beforeEach(function () {
    actAsAdmin($this);
});

it('displays sale return details with items', function () {
    $customer = Customer::factory()->create(['name' => 'Test Customer']);
    $product = Product::factory()->create(['name' => 'Test Filter']);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 3,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 200,
        'reason' => 'Quality issues',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 2,
        'sale_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    $this->get(route('sale-returns.show', $return))
        ->assertOk()
        ->assertSee($return->number)
        ->assertSee($sale->number)
        ->assertSee('Test Customer')
        ->assertSee('Quality issues')
        ->assertSee('Test Filter')
        ->assertSee('200.00', false);
});

it('shows correct total for multiple returned items', function () {
    $customer = Customer::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 500,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 3,
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 4,
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 400,
        'reason' => null,
        'cash_refund' => false,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 2,
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 4,
        'sale_return_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    $this->get(route('sale-returns.show', $return))
        ->assertOk()
        ->assertSee('400.00', false);
});
