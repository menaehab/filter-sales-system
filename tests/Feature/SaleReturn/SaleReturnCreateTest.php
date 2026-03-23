<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a customer return and decreases inventory', function () {
    $supplier = Supplier::factory()->create();
    $product1 = Product::factory()->create(['cost_price' => 50, 'quantity' => 10]);
    $product2 = Product::factory()->create(['cost_price' => 30, 'quantity' => 5]);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 230,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product1->name,
        'cost_price' => 50,
        'quantity' => 3,
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product2->name,
        'cost_price' => 30,
        'quantity' => 4,
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
    ]);

    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', $purchase->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '2')
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '3')
        ->set('reason', 'Quality defect')
        ->set('cash_refund', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $return = PurchaseReturn::with('items')->sole();

    $this->assertEquals($purchase->id, $return->purchase_id);
    $this->assertEquals('Quality defect', $return->reason);
    $this->assertTrue((bool) $return->cash_refund);
    $this->assertEquals(190.0, (float) $return->total_price); // 2*50 + 3*30
    $this->assertEquals(2, $return->items->count());

    $product1->refresh();
    $product2->refresh();

    $this->assertEquals(8.0, (float) $product1->quantity); // 10 - 2
    $this->assertEquals(2.0, (float) $product2->quantity); // 5 - 3

    $this->assertDatabaseHas('purchase_return_items', [
        'purchase_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'cost_price' => '50.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => PurchaseReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => '-2.00',
    ]);
});

it('validates that at least one item is selected for return', function () {
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
        'quantity' => 1,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', $purchase->number)
        ->call('save')
        ->assertHasErrors('items');

    $this->assertDatabaseCount('purchase_returns', 0);
});

it('validates return quantity does not exceed available quantity', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['quantity' => 5]);

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
        'quantity' => 3,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', $purchase->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '5')
        ->call('save')
        ->assertHasErrors('items.0.return_quantity');

    $this->assertDatabaseCount('purchase_returns', 0);
});

it('allows creating a return without cash refund', function () {
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

    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', $purchase->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '1')
        ->set('cash_refund', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $return = PurchaseReturn::sole();
    $this->assertFalse((bool) $return->cash_refund);
});

it('shows validation error when purchase number is not found', function () {
    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', 'NOT-EXISTS-001')
        ->call('save')
        ->assertHasErrors(['purchase_number'])
        ->assertHasErrors(['items']);

    $this->assertDatabaseCount('purchase_returns', 0);
});

it('creates supplier credit when return is saved without cash refund', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['cost_price' => 60, 'quantity' => 10]);

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
        'cost_price' => 60,
        'quantity' => 5,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $payment = SupplierPayment::create([
        'supplier_id' => $supplier->id,
        'amount' => 300,
        'payment_method' => 'cash',
    ]);

    SupplierPaymentAllocation::create([
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $purchase->id,
        'amount' => 300,
    ]);

    Livewire::test('purchase-returns.purchase-return-create')
        ->set('purchase_number', $purchase->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '2')
        ->set('cash_refund', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchase-returns'));

    $supplier->refresh();
    $this->assertEquals(120.0, (float) $supplier->available_credit);
    $this->assertEquals(-120.0, (float) $supplier->balance);
});
