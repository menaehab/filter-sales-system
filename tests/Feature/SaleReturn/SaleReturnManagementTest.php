<?php

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('displays list of purchase returns', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['quantity' => 20]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 100,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 50,
        'reason' => 'Defective',
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

    $this->get(route('purchase-returns'))
        ->assertOk()
        ->assertSee($return->number)
        ->assertSee($purchase->number)
        ->assertSee('50.00', false);
});

it('filters purchase returns by search number', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['quantity' => 30]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 100,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $return1 = PurchaseReturn::create([
        'total_price' => 100,
        'reason' => 'Test 1',
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    $supply2 = Supplier::factory()->create();
    $purchase2 = Purchase::create([
        'supplier_name' => $supply2->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supply2->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 100,
        'quantity' => 5,
        'purchase_id' => $purchase2->id,
        'product_id' => $product->id,
    ]);

    $return2 = PurchaseReturn::create([
        'total_price' => 100,
        'reason' => 'Test 2',
        'cash_refund' => false,
        'purchase_id' => $purchase2->id,
        'user_id' => auth()->id(),
    ]);

    Livewire::test('purchase-returns.purchase-return-management')
        ->set('search', $return1->number)
        ->assertSee($return1->number)
        ->assertDontSee($return2->number);
});

it('deletes a purchase return and restores product inventory', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['quantity' => 3]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 100,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 50,
        'reason' => 'Defective',
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

    ProductMovement::create([
        'quantity' => -2,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product->id,
    ]);

    $product->decrement('quantity', 2);
    $product->refresh();
    $this->assertEquals(1.0, (float) $product->quantity);

    Livewire::test('purchase-returns.purchase-return-management')
        ->call('setDelete', $return->id)
        ->call('delete')
        ->assertHasNoErrors();

    $product->refresh();
    $this->assertEquals(3.0, (float) $product->quantity);
    $this->assertDatabaseMissing('purchase_returns', ['id' => $return->id]);
});
