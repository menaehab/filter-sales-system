<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
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

        $this->assertEquals('cash', $purchase->payment_type);
        $this->assertEquals(120.0, (float) $purchase->total_price);
        $this->assertEquals(120.0, (float) $purchase->down_payment);
        $this->assertNull($purchase->installment_months);

    $product->refresh();

        $this->assertEquals(60.0, (float) $product->cost_price);
        $this->assertEquals(5.0, (float) $product->quantity);

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

    $this->assertEquals('installment', $purchase->payment_type);
    $this->assertEquals(180.0, (float) $purchase->total_price);
    $this->assertEquals(30.0, (float) $purchase->down_payment);
    $this->assertEquals(50.0, (float) $purchase->installment_amount);
    $this->assertEquals(3, $purchase->installment_months);
    $this->assertEquals('2026-04-11', $purchase->next_installment_date?->toDateString());

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

it('applies available supplier credit to a new purchase before cash payment', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create([
        'cost_price' => 25,
        'quantity' => 1,
    ]);

    $oldPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $oldPurchasePayment = SupplierPayment::create([
        'supplier_id' => $supplier->id,
        'amount' => 300,
        'payment_method' => 'cash',
    ]);

    SupplierPaymentAllocation::create([
        'supplier_payment_id' => $oldPurchasePayment->id,
        'purchase_id' => $oldPurchase->id,
        'amount' => 300,
    ]);

    $oldPurchase->returns()->create([
        'total_price' => 150,
        'reason' => 'Credit kept for next invoice',
        'cash_refund' => false,
        'user_id' => auth()->id(),
    ]);

    Livewire::test('purchases.purchase-create')
        ->set('supplier_id', $supplier->id)
        ->set('payment_type', 'cash')
        ->set('items', [[
            'product_id' => (string) $product->id,
            'product_name' => $product->name,
            'cost_price' => '50',
            'quantity' => '10',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('purchases'));

    $purchase = Purchase::whereKeyNot($oldPurchase->id)->latest('id')->first();
    $supplier->refresh();

    $this->assertEquals(500.0, (float) $purchase->total_price);
    $this->assertEquals(500.0, (float) $purchase->paid_amount);
    $this->assertEquals(0.0, (float) $purchase->remaining_amount);
    $this->assertEquals(0.0, (float) $supplier->available_credit);
    $this->assertEquals(0.0, (float) $supplier->balance);

    $this->assertDatabaseHas('supplier_payments', [
        'supplier_id' => $supplier->id,
        'amount' => '350.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('supplier_payments', [
        'supplier_id' => $supplier->id,
        'amount' => '150.00',
        'payment_method' => 'supplier_credit',
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'purchase_id' => $purchase->id,
        'amount' => '150.00',
    ]);
});
