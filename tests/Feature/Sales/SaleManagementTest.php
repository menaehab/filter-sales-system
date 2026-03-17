<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('filters sales by search, payment type, and status', function () {
    $northCustomer = Customer::create(['name' => 'North Customer']);
    $eastCustomer = Customer::create(['name' => 'East Customer']);

    $paidSale = Sale::create([
        'dealer_name' => 'North Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $northCustomer->id,
    ]);

    $partialSale = Sale::create([
        'dealer_name' => 'North Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'customer_id' => $northCustomer->id,
    ]);

    $unpaidSale = Sale::create([
        'dealer_name' => 'East Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 250,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 5,
        'user_id' => auth()->id(),
        'customer_id' => $eastCustomer->id,
    ]);

    $payment = CustomerPayment::create([
        'amount' => 25,
        'payment_method' => 'cash',
        'customer_id' => $northCustomer->id,
        'user_id' => auth()->id(),
    ]);

    $paidPayment = CustomerPayment::create([
        'amount' => 100,
        'payment_method' => 'cash',
        'customer_id' => $northCustomer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 100,
        'customer_payment_id' => $paidPayment->id,
        'sale_id' => $paidSale->id,
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 25,
        'customer_payment_id' => $payment->id,
        'sale_id' => $partialSale->id,
    ]);

    $component = Livewire::test('sales.sale-management')
        ->set('search', 'North');

    $sales = $component->get('sales');

    $this->assertSame(2, $sales->total());
    $this->assertEqualsCanonicalizing(
        [$paidSale->id, $partialSale->id],
        collect($sales->items())->pluck('id')->all(),
    );

    $component->set('search', '');
    $component->set('filterPaymentType', 'installment');

    $installmentIds = collect($component->get('sales')->items())->pluck('id')->all();
    $this->assertEqualsCanonicalizing([$partialSale->id, $unpaidSale->id], $installmentIds);

    $component->set('filterPaymentType', '');
    $component->set('filterStatus', 'paid');
    $this->assertSame([$paidSale->id], collect($component->get('sales')->items())->pluck('id')->all());

    $component->set('filterStatus', 'partial');
    $this->assertSame([$partialSale->id], collect($component->get('sales')->items())->pluck('id')->all());

    $component->set('filterStatus', 'unpaid');
    $this->assertSame([$unpaidSale->id], collect($component->get('sales')->items())->pluck('id')->all());
});

it('allocates customer payments to the oldest unpaid installment sales first', function () {
    Carbon::setTestNow('2026-03-11 09:00:00');

    $customer = Customer::create(['name' => 'Queued Customer']);

    $oldestSale = Sale::create([
        'dealer_name' => 'Old Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 100,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => Carbon::parse('2026-03-01 08:00:00'),
        'updated_at' => Carbon::parse('2026-03-01 08:00:00'),
    ]);

    $newerSale = Sale::create([
        'dealer_name' => 'New Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 3,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => Carbon::parse('2026-03-02 08:00:00'),
        'updated_at' => Carbon::parse('2026-03-02 08:00:00'),
    ]);

    $component = Livewire::test('sales.sale-management')
        ->call('openPayModal', $newerSale->id);

    $this->assertEquals($newerSale->id, $component->get('paySaleId'));
    $this->assertEquals($oldestSale->id, $component->get('payFromSaleId'));
    $this->assertEquals(100.0, (float) $component->get('payAmount'));

    $component->set('payAmount', '330')
        ->set('payMethod', 'bank_transfer')
        ->set('payNote', 'Wire transfer')
        ->call('submitPayment')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-pay-sale');

    $payment = CustomerPayment::sole();

    $this->assertDatabaseHas('customer_payments', [
        'id' => $payment->id,
        'customer_id' => $customer->id,
        'amount' => '330.00',
        'payment_method' => 'bank_transfer',
        'note' => 'Wire transfer',
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'customer_payment_id' => $payment->id,
        'sale_id' => $oldestSale->id,
        'amount' => '300.00',
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'customer_payment_id' => $payment->id,
        'sale_id' => $newerSale->id,
        'amount' => '30.00',
    ]);

    $oldestSale->refresh();
    $newerSale->refresh();

    $this->assertTrue($oldestSale->isFullyPaid());
    $this->assertNull($oldestSale->next_installment_date);
    $this->assertFalse($newerSale->isFullyPaid());
    $this->assertEquals('2026-04-11', $newerSale->next_installment_date?->toDateString());

    Carbon::setTestNow();
});

it('deletes a sale from the management component', function () {
    $customer = Customer::create(['name' => 'Delete Customer']);

    $sale = Sale::create([
        'dealer_name' => 'Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 90,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    Livewire::test('sales.sale-management')
        ->call('setDelete', $sale->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-sale');

    $this->assertDatabaseMissing('sales', [
        'id' => $sale->id,
    ]);
});

it('deletes orphaned customer payments when deleting a sale', function () {
    $customer = Customer::create(['name' => 'Paid Customer']);

    $sale = Sale::create([
        'dealer_name' => 'Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    $payment = CustomerPayment::create([
        'amount' => 200,
        'payment_method' => 'cash',
        'customer_id' => $customer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 200,
        'customer_payment_id' => $payment->id,
        'sale_id' => $sale->id,
    ]);

    Livewire::test('sales.sale-management')
        ->call('setDelete', $sale->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-sale');

    $this->assertDatabaseMissing('sales', [
        'id' => $sale->id,
    ]);

    $this->assertDatabaseMissing('customer_payments', [
        'id' => $payment->id,
    ]);
});

it('keeps shared customer payments when deleting one sale', function () {
    $customer = Customer::create(['name' => 'Shared Customer']);

    $firstSale = Sale::create([
        'dealer_name' => 'First Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 2,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    $secondSale = Sale::create([
        'dealer_name' => 'Second Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 2,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    $sharedPayment = CustomerPayment::create([
        'amount' => 120,
        'payment_method' => 'cash',
        'customer_id' => $customer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 100,
        'customer_payment_id' => $sharedPayment->id,
        'sale_id' => $firstSale->id,
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 20,
        'customer_payment_id' => $sharedPayment->id,
        'sale_id' => $secondSale->id,
    ]);

    Livewire::test('sales.sale-management')
        ->call('setDelete', $firstSale->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-sale');

    $this->assertDatabaseMissing('sales', [
        'id' => $firstSale->id,
    ]);

    $this->assertDatabaseHas('customer_payments', [
        'id' => $sharedPayment->id,
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'customer_payment_id' => $sharedPayment->id,
        'sale_id' => $secondSale->id,
        'amount' => '20.00',
    ]);
});
