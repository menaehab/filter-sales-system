<?php

use App\Models\SupplierPayment;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('deletes a supplier payment and cascades its allocations', function () {
    $supplierPayment = SupplierPayment::factory()->hasAllocations(2)->create();
    $allocationIds = $supplierPayment->allocations()->pluck('id');

    Livewire::test('supplier-payments.supplier-payment-management')
        ->call('setDelete', $supplierPayment->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-supplier-payment');

    $this->assertDatabaseMissing('supplier_payments', [
        'id' => $supplierPayment->id,
    ]);

    foreach ($allocationIds as $allocationId) {
        $this->assertDatabaseMissing('supplier_payment_allocations', [
            'id' => $allocationId,
        ]);
    }
});

it('filters supplier payments by search term', function () {
    $supplierPayment1 = SupplierPayment::factory()->create([
        'amount' => 100,
    ]);
    $supplierPayment2 = SupplierPayment::factory()->create([
        'amount' => 200,
    ]);

    $component = Livewire::test('supplier-payments.supplier-payment-management')
        ->set('search', $supplierPayment1->user->name);

    $payments = $component->get('supplierPayments');

    $this->assertSame(1, $payments->total());
    $this->assertSame([100.0], collect($payments->items())->pluck('amount')->map(fn ($a) => (float) $a)->all());
});

it('paginates supplier payments using per page selection', function () {
    SupplierPayment::factory()->count(15)->create();

    $component = Livewire::test('supplier-payments.supplier-payment-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('supplierPayments'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('supplierPayments'));
});

it('resets page when search or per page changes', function () {
    SupplierPayment::factory()->count(30)->create();

    $component = Livewire::test('supplier-payments.supplier-payment-management');

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPayments')->currentPage());

    $component->set('search', 'a');
    $this->assertSame(1, $component->get('supplierPayments')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPayments')->currentPage());

    $component->set('perPage', 25);
    $this->assertSame(1, $component->get('supplierPayments')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPayments')->currentPage());
});
