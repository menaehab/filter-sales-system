<?php

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
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
        'down_payment' => 30,
        'installment_amount' => null,
        'installment_months' => null,
        'next_installment_date' => null,
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

    Livewire::test('purchases.purchase-edit', ['purchase' => $purchase])
        ->set('supplier_id', $newSupplier->id)
        ->set('payment_type', 'cash')
        ->set('items', [[
            'product_id' => (string) $newProduct->id,
            'product_name' => $newProduct->name,
            'cost_price' => '30',
            'quantity' => '4',
        ]])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchases'));

    $purchase->refresh();
    $oldProduct->refresh();
    $newProduct->refresh();

    expect($purchase->supplier_id)->toBe($newSupplier->id);
    expect($purchase->supplier_name)->toBe('New Supplier');
    expect((float) $purchase->total_price)->toBe(120.0);
    expect((float) $purchase->down_payment)->toBe(120.0);

    expect((float) $oldProduct->quantity)->toBe(5.0);
    expect((float) $newProduct->quantity)->toBe(5.0);
    expect((float) $newProduct->cost_price)->toBe(30.0);

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

    expect(ProductMovement::where('movable_type', Purchase::class)
        ->where('movable_id', $purchase->id)
        ->count())->toBe(1);

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
        ->set('items', [[
            'product_id' => '',
            'product_name' => '',
            'cost_price' => '',
            'quantity' => '',
        ]])
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
