<?php

use App\Models\CustomerPayment;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('deletes a customer payment and cascades its allocations', function () {
    $customerPayment = CustomerPayment::factory()->hasAllocations(2)->create();
    $allocationIds = $customerPayment->allocations()->pluck('id');

    Livewire::test('customer-payments.customer-payment-management')
        ->call('setDelete', $customerPayment->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-customer-payment');

    $this->assertDatabaseMissing('customer_payments', [
        'id' => $customerPayment->id,
    ]);

    foreach ($allocationIds as $allocationId) {
        $this->assertDatabaseMissing('customer_payment_allocations', [
            'id' => $allocationId,
        ]);
    }
});

it('filters customer payments by search term', function () {
    $customerPayment1 = CustomerPayment::factory()->create([
        'amount' => 100,
    ]);
    $customerPayment2 = CustomerPayment::factory()->create([
        'amount' => 200,
    ]);

    $component = Livewire::test('customer-payments.customer-payment-management')
        ->set('search', $customerPayment1->user->name);

    $payments = $component->get('customerPayments');

    $this->assertSame(1, $payments->total());
    $this->assertSame([100.0], collect($payments->items())->pluck('amount')->map(fn ($a) => (float) $a)->all());
});

it('paginates customer payments using per page selection', function () {
    CustomerPayment::factory()->count(15)->create();

    $component = Livewire::test('customer-payments.customer-payment-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('customerPayments'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('customerPayments'));
});

it('resets page when search or per page changes', function () {
    CustomerPayment::factory()->count(30)->create();

    $component = Livewire::test('customer-payments.customer-payment-management');

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('customerPayments')->currentPage());

    $component->set('search', 'a');
    $this->assertSame(1, $component->get('customerPayments')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('customerPayments')->currentPage());

    $component->set('perPage', 25);
    $this->assertSame(1, $component->get('customerPayments')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('customerPayments')->currentPage());
});
