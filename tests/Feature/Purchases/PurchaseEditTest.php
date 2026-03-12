<?php

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('updates a purchase and recalculates stock and movements', function () {
    $oldSupplier = Supplier::factory()->create(['name' => 'Old Supplier']);
    $newSupplier = Supplier::factory()->create(['name' => 'New Supplier']);

    $oldProduct = Product::factory()->create([
        'name' => 'Old Product',
        'cost_price' => 15,
        'quantity' => 7,
    ]);
    $newProduct = Product::factory()->create([
        'name' => 'New Product',
        'cost_price' => 25,
        'quantity' => 1,
    ]);

    $purchase = Purchase::create([
        'supplier_name' => $oldSupplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 30,
        'payment_type' => 'cash',
        'installment_amount' => null,
        'installment_months' => null,
        'user_id' => auth()->id(),
        'supplier_id' => $oldSupplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $oldProduct->name,
        'cost_price' => 15,
        'quantity' => 2,
        'purchase_id' => $purchase->id,
        'product_id' => $oldProduct->id,
    ]);

    ProductMovement::create([
        'quantity' => 2,
        'movable_type' => Purchase::class,
        'movable_id' => $purchase->id,
        'product_id' => $oldProduct->id,
    ]);

    // Create initial payment allocation (simulates down payment)
    $payment = SupplierPayment::create([
        'amount' => 30,
        'payment_method' => 'cash',
        'supplier_id' => $oldSupplier->id,
    ]);
    SupplierPaymentAllocation::create([
        'amount' => 30,
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $purchase->id,
    ]);

    Livewire::test('purchases.purchase-edit', ['purchase' => $purchase])
        ->set('supplier_id', $newSupplier->id)
        ->set('payment_type', 'cash')
        ->set('items', [
            [
                'product_id' => (string) $newProduct->id,
                'product_name' => $newProduct->name,
                'cost_price' => '30',
                'quantity' => '4',
            ]
        ])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchases'));

    $purchase->refresh()->load('paymentAllocations');
    $oldProduct->refresh();
    $newProduct->refresh();

    $this->assertEquals($newSupplier->id, $purchase->supplier_id);
    $this->assertEquals('New Supplier', $purchase->supplier_name);
    $this->assertEquals(120.0, (float) $purchase->total_price);
    $this->assertGreaterThan(0, $purchase->paymentAllocations->count());
    $this->assertEquals(5.0, (float) $oldProduct->quantity);
    $this->assertEquals(5.0, (float) $newProduct->quantity);
    $this->assertEquals(30.0, (float) $newProduct->cost_price);

    $this->assertDatabaseMissing('purchase_items', [
        'purchase_id' => $purchase->id,
        'product_id' => $oldProduct->id,
    ]);

    $this->assertDatabaseHas('purchase_items', [
        'purchase_id' => $purchase->id,
        'product_id' => $newProduct->id,
        'product_name' => $newProduct->name,
        'cost_price' => '30.00',
        'quantity' => '4.00',
    ]);

    $this->assertDatabaseCount(
        'product_movements',
        1,
        null,
    );

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => Purchase::class,
        'movable_id' => $purchase->id,
        'product_id' => $newProduct->id,
        'quantity' => '4.00',
    ]);
});

it('validates edited purchase data before updating', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 20,
        'payment_type' => 'cash',
        'down_payment' => 20,
        'installment_amount' => null,
        'installment_months' => null,
        'next_installment_date' => null,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 10,
        'quantity' => 2,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('purchases.purchase-edit', ['purchase' => $purchase])
        ->set('supplier_id', null)
        ->set('payment_type', 'installment')
        ->set('down_payment', '')
        ->set('installment_months', '')
        ->set('items', [
            [
                'product_id' => '',
                'product_name' => '',
                'cost_price' => '',
                'quantity' => '',
            ]
        ])
        ->call('update')
        ->assertHasErrors([
            'supplier_id' => 'required',
            'down_payment' => 'required_if',
            'installment_months' => 'required_if',
            'items.0.product_id' => 'required',
            'items.0.cost_price' => 'required',
            'items.0.quantity' => 'required',
        ]);
});
