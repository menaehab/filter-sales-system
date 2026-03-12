<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;

beforeEach(function () {
    actAsAdmin($this);
});

it('displays purchase return details with items', function () {
    $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
    $product = Product::factory()->create(['name' => 'Test Filter']);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 100,
        'quantity' => 3,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 200,
        'reason' => 'Quality issues',
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 100,
        'quantity' => 2,
        'purchase_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    $this->get(route('purchase-returns.show', $return))
        ->assertOk()
        ->assertSee($return->number)
        ->assertSee($purchase->number)
        ->assertSee('Test Supplier')
        ->assertSee('Quality issues')
        ->assertSee('Test Filter')
        ->assertSee('200.00', false);
});

it('shows correct total for multiple returned items', function () {
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 500,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product1->name,
        'cost_price' => 100,
        'quantity' => 3,
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product2->name,
        'cost_price' => 50,
        'quantity' => 4,
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 400,
        'reason' => null,
        'cash_refund' => false,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 100,
        'quantity' => 2,
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 50,
        'quantity' => 4,
        'purchase_return_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    $this->get(route('purchase-returns.show', $return))
        ->assertOk()
        ->assertSee('400.00', false);
});

