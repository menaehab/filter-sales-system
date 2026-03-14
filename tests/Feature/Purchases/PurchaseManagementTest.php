<?php

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('filters purchases by search, payment type, and status', function () {
    $supplier = Supplier::factory()->create(['name' => 'North Supplier']);

    $paidPurchase = Purchase::create([
        'supplier_name' => 'North Supplier',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'installment_amount' => null,
        'installment_months' => null,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $partialPurchase = Purchase::create([
        'supplier_name' => 'North Supplier',
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $unpaidPurchase = Purchase::create([
        'supplier_name' => 'East Supplier',
        'user_name' => auth()->user()->name,
        'total_price' => 250,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 5,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $payment = SupplierPayment::create([
        'amount' => 25,
        'payment_method' => 'cash',
        'supplier_id' => $supplier->id,
    ]);

    $paidPayment = SupplierPayment::create([
        'amount' => 100,
        'payment_method' => 'cash',
        'supplier_id' => $supplier->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 100,
        'supplier_payment_id' => $paidPayment->id,
        'purchase_id' => $paidPurchase->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 25,
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $partialPurchase->id,
    ]);

    $component = Livewire::test('purchases.purchase-management')
        ->set('search', 'North');

    $purchases = $component->get('purchases');

    $this->assertSame(2, $purchases->total());
    $this->assertEqualsCanonicalizing(
        [$paidPurchase->id, $partialPurchase->id],
        collect($purchases->items())->pluck('id')->all(),
    );

    $component->set('search', '');
    $component->set('filterPaymentType', 'installment');

    $installmentIds = collect($component->get('purchases')->items())->pluck('id')->all();
    $this->assertEqualsCanonicalizing([$partialPurchase->id, $unpaidPurchase->id], $installmentIds);

    $component->set('filterPaymentType', '');
    $component->set('filterStatus', 'paid');
    $this->assertSame([$paidPurchase->id], collect($component->get('purchases')->items())->pluck('id')->all());

    $component->set('filterStatus', 'partial');
    $this->assertSame([$partialPurchase->id], collect($component->get('purchases')->items())->pluck('id')->all());

    $component->set('filterStatus', 'unpaid');
    $this->assertSame([$unpaidPurchase->id], collect($component->get('purchases')->items())->pluck('id')->all());
});

it('allocates supplier payments to the oldest unpaid installment purchases first', function () {
    Carbon::setTestNow('2026-03-11 09:00:00');

    $supplier = Supplier::factory()->create();

    $oldestPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 100,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
        'created_at' => Carbon::parse('2026-03-01 08:00:00'),
        'updated_at' => Carbon::parse('2026-03-01 08:00:00'),
    ]);

    $newerPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
        'created_at' => Carbon::parse('2026-03-02 08:00:00'),
        'updated_at' => Carbon::parse('2026-03-02 08:00:00'),
    ]);

    $component = Livewire::test('purchases.purchase-management')
        ->call('openPayModal', $newerPurchase->id);

    $this->assertEquals($newerPurchase->id, $component->get('payPurchaseId'));
    $this->assertEquals($oldestPurchase->id, $component->get('payFromPurchaseId'));
    $this->assertEquals(100.0, (float) $component->get('payAmount'));

    $component->set('payAmount', '330')
        ->set('payMethod', 'bank_transfer')
        ->set('payNote', 'Wire transfer')
        ->call('submitPayment')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-pay-purchase');

    $payment = SupplierPayment::sole();

    $this->assertDatabaseHas('supplier_payments', [
        'id' => $payment->id,
        'supplier_id' => $supplier->id,
        'amount' => '330.00',
        'payment_method' => 'bank_transfer',
        'note' => 'Wire transfer',
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $oldestPurchase->id,
        'amount' => '300.00',
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $newerPurchase->id,
        'amount' => '30.00',
    ]);

    $oldestPurchase->refresh();
    $newerPurchase->refresh();

    $this->assertTrue($oldestPurchase->isFullyPaid());
    $this->assertNull($oldestPurchase->next_installment_date);
    $this->assertFalse($newerPurchase->isFullyPaid());
    $this->assertEquals('2026-04-11', $newerPurchase->next_installment_date?->toDateString());

    Carbon::setTestNow();
});

it('deletes a purchase from the management component', function () {
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 90,
        'payment_type' => 'cash',
        'down_payment' => 90,
        'installment_amount' => null,
        'installment_months' => null,
        'next_installment_date' => null,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    Livewire::test('purchases.purchase-management')
        ->call('setDelete', $purchase->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-purchase');

    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});

it('deletes orphaned supplier payments when deleting a purchase', function () {
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'cash',
        'installment_amount' => null,
        'installment_months' => null,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $payment = SupplierPayment::create([
        'amount' => 200,
        'payment_method' => 'cash',
        'supplier_id' => $supplier->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 200,
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $purchase->id,
    ]);

    Livewire::test('purchases.purchase-management')
        ->call('setDelete', $purchase->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-purchase');

    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);

    $this->assertDatabaseMissing('supplier_payments', [
        'id' => $payment->id,
    ]);
});

it('keeps shared supplier payments when deleting one purchase', function () {
    $supplier = Supplier::factory()->create();

    $firstPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 2,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $secondPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 2,
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    $sharedPayment = SupplierPayment::create([
        'amount' => 120,
        'payment_method' => 'cash',
        'supplier_id' => $supplier->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 100,
        'supplier_payment_id' => $sharedPayment->id,
        'purchase_id' => $firstPurchase->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 20,
        'supplier_payment_id' => $sharedPayment->id,
        'purchase_id' => $secondPurchase->id,
    ]);

    Livewire::test('purchases.purchase-management')
        ->call('setDelete', $firstPurchase->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-purchase');

    $this->assertDatabaseMissing('purchases', [
        'id' => $firstPurchase->id,
    ]);

    $this->assertDatabaseHas('supplier_payments', [
        'id' => $sharedPayment->id,
    ]);

    $this->assertDatabaseHas('supplier_payment_allocations', [
        'supplier_payment_id' => $sharedPayment->id,
        'purchase_id' => $secondPurchase->id,
        'amount' => '20.00',
    ]);
});
