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

it('updates a purchase return and recalculates stock', function () {
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 10]);
    $product2 = Product::factory()->create(['quantity' => 15]);

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
        'cost_price' => 50,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product2->name,
        'cost_price' => 30,
        'quantity' => 10,
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
    ]);

    // Create initial return
    $return = PurchaseReturn::create([
        'total_price' => 250,
        'reason' => 'Defective',
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 50,
        'quantity' => 1,
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 30,
        'quantity' => 5,
        'purchase_return_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    ProductMovement::create([
        'quantity' => -1,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    ProductMovement::create([
        'quantity' => -5,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    $product1->decrement('quantity', 1);
    $product2->decrement('quantity', 5);

    $product1->refresh();
    $product2->refresh();
    $this->assertEquals(9.0, (float) $product1->quantity);
    $this->assertEquals(10.0, (float) $product2->quantity);

    // Now edit the return
    Livewire::test('purchase-returns.purchase-return-edit', ['purchaseReturn' => $return])
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '3')
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '2')
        ->set('reason', 'Updated reason')
        ->set('cash_refund', false)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $return->refresh();
    $product1->refresh();
    $product2->refresh();

    $this->assertEquals('Updated reason', $return->reason);
    $this->assertFalse((bool) $return->cash_refund);
    $this->assertEquals(210.0, (float) $return->total_price); // 3*50 + 2*30

    // Stock should be recalculated: original - new_deduction
    // product1: 10 - 3 = 7
    // product2: 15 - 2 = 13
    $this->assertEquals(7.0, (float) $product1->quantity);
    $this->assertEquals(13.0, (float) $product2->quantity);

    $this->assertDatabaseHas('purchase_return_items', [
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 3,
    ]);

    $this->assertDatabaseHas('purchase_return_items', [
        'purchase_return_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => 2,
    ]);
});

it('validates edited return data before updating', function () {
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 10]);
    $product2 = Product::factory()->create(['quantity' => 10]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product1->name,
        'cost_price' => 50,
        'quantity' => 2,
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product2->name,
        'cost_price' => 100,
        'quantity' => 1,
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 100,
        'reason' => null,
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 50,
        'quantity' => 1,
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    // Try to deselect the selected item - component initializes it as selected
    // Since it's selected by component, calling update() without changing it should work
    // We need to deselect it explicitly
    Livewire::test('purchase-returns.purchase-return-edit', ['purchaseReturn' => $return])
        ->set('items.0.selected', false)
        ->set('items.1.selected', false)
        ->call('update')
        ->assertHasErrors('items');

    $this->assertDatabaseCount('purchase_return_items', 1);
});

it('allows updating return with no reason', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);

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
        'quantity' => 2,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 100,
        'reason' => 'Old reason',
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 100,
        'quantity' => 1,
        'purchase_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('purchase-returns.purchase-return-edit', ['purchaseReturn' => $return])
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '1')
        ->set('reason', '')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $return->refresh();
    $this->assertNull($return->reason);
});

it('replaces old return items and movements when product selection changes', function () {
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 20]);
    $product2 = Product::factory()->create(['quantity' => 20]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 400,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product1->name,
        'cost_price' => 40,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product2->name,
        'cost_price' => 60,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
    ]);

    $return = PurchaseReturn::create([
        'total_price' => 80,
        'reason' => 'Initial',
        'cash_refund' => true,
        'purchase_id' => $purchase->id,
        'user_id' => auth()->id(),
    ]);

    PurchaseReturnItem::create([
        'cost_price' => 40,
        'quantity' => 2,
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    ProductMovement::create([
        'quantity' => -2,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    $product1->decrement('quantity', 2);

    Livewire::test('purchase-returns.purchase-return-edit', ['purchaseReturn' => $return])
        ->set('items.0.selected', false)
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '4')
        ->set('cash_refund', false)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $return->refresh();
    $product1->refresh();
    $product2->refresh();

    $this->assertEquals(240.0, (float) $return->total_price);
    $this->assertFalse((bool) $return->cash_refund);
    $this->assertEquals(20.0, (float) $product1->quantity);
    $this->assertEquals(16.0, (float) $product2->quantity);

    $this->assertDatabaseMissing('purchase_return_items', [
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('purchase_return_items', [
        'purchase_return_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => 4,
    ]);

    $this->assertDatabaseMissing('product_movements', [
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => '-2.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => '-4.00',
    ]);
});
