<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a cash purchase and syncs inventory and payment records', function () {
    $supplier = Supplier::factory()->create(['name' => 'Acme Supplies']);
    $product = Product::factory()->create([
        'name' => 'Primary Filter',
        'cost_price' => 50,
        'quantity' => 3,
    ]);

    Livewire::test('purchases.purchase-create')
        ->set('supplier_id', $supplier->id)
        ->set('payment_type', 'cash')
        ->set('items', [[
            'product_id' => (string) $product->id,
            'product_name' => $product->name,
            'cost_price' => '60',
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchases'));

    $purchase = Purchase::with(['items', 'paymentAllocations'])->sole();

    expect($purchase->payment_type)->toBe('cash');
    expect((float) $purchase->total_price)->toBe(120.0);
    expect((float) $purchase->down_payment)->toBe(120.0);
    expect($purchase->installment_months)->toBeNull();

    $product->refresh();

    expect((float) $product->cost_price)->toBe(60.0);
    expect((float) $product->quantity)->toBe(5.0);

    $this->assertDatabaseHas('purchase_items', [
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'cost_price' => '60.00',
        'quantity' => '2.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => Purchase::class,
        'movable_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => '2.00',
    ]);

    $payment = SupplierPayment::sole();

    $this->assertDatabaseHas('supplier_payments', [
        'id' => $payment->id,
        'supplier_id' => $supplier->id,
        'amount' => '120.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $purchase->id,
        'amount' => '120.00',
    ]);
});

it('creates an installment purchase with down payment and next installment date', function () {
    Carbon::setTestNow('2026-03-11 10:00:00');

    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create([
        'cost_price' => 40,
        'quantity' => 0,
    ]);

    Livewire::test('purchases.purchase-create')
        ->set('supplier_id', $supplier->id)
        ->set('payment_type', 'installment')
        ->set('down_payment', '30')
        ->set('installment_months', '3')
        ->set('items', [[
            'product_id' => (string) $product->id,
            'product_name' => $product->name,
            'cost_price' => '45',
            'quantity' => '4',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchases'));

    $purchase = Purchase::sole();

    expect($purchase->payment_type)->toBe('installment');
    expect((float) $purchase->total_price)->toBe(180.0);
    expect((float) $purchase->down_payment)->toBe(30.0);
    expect((float) $purchase->installment_amount)->toBe(50.0);
    expect($purchase->installment_months)->toBe(3);
    expect($purchase->next_installment_date?->toDateString())->toBe('2026-04-11');

    $this->assertDatabaseHas('supplier_payments', [
        'supplier_id' => $supplier->id,
        'amount' => '30.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'purchase_id' => $purchase->id,
        'amount' => '30.00',
    ]);

    Carbon::setTestNow();
});

it('validates purchase fields before saving', function () {
    Livewire::test('purchases.purchase-create')
        ->set('payment_type', 'installment')
        ->set('down_payment', '')
        ->set('installment_months', '')
        ->set('items', [[
            'product_id' => '',
            'product_name' => '',
            'cost_price' => '',
            'quantity' => '',
        ]])
        ->call('save')
        ->assertHasErrors([
            'supplier_id' => 'required',
            'down_payment' => 'required_if',
            'installment_months' => 'required_if',
            'items.0.product_id' => 'required',
            'items.0.cost_price' => 'required',
            'items.0.quantity' => 'required',
        ]);
});
