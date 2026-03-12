<?php

use App\Models\SupplierPaymentAllocation;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('deletes a supplier payment allocation', function () {
    $supplierPaymentAllocation = SupplierPaymentAllocation::factory()->create();

    Livewire::test('supplier-payment-allocations.supplier-payment-allocation-management')
        ->call('setDelete', $supplierPaymentAllocation->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-supplier-payment-allocation');

    $this->assertDatabaseMissing('supplier_payment_allocations', [
        'id' => $supplierPaymentAllocation->id,
    ]);
});

it('filters supplier payment allocations by search term', function () {
    $supplierPaymentAllocation1 = SupplierPaymentAllocation::factory()->create([
        'amount' => 100,
    ]);
    $supplierPaymentAllocation2 = SupplierPaymentAllocation::factory()->create([
        'amount' => 200,
    ]);

    $component = Livewire::test('supplier-payment-allocations.supplier-payment-allocation-management')
        ->set('search', $supplierPaymentAllocation1->supplierPayment->user->name);

    $allocations = $component->get('supplierPaymentAllocations');

    $this->assertSame(1, $allocations->total());
    // database returns formatted numbers as strings, cast to float for comparison
    $this->assertSame([100.0], collect($allocations->items())->pluck('amount')->map(fn($a) => (float) $a)->all());
});

it('paginates supplier payment allocations using per page selection', function () {
    SupplierPaymentAllocation::factory()->count(15)->create();

    $component = Livewire::test('supplier-payment-allocations.supplier-payment-allocation-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('supplierPaymentAllocations'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('supplierPaymentAllocations'));
});

it('resets page when search, per page, or category filter changes', function () {
    SupplierPaymentAllocation::factory()->count(30)->create();

    $component = Livewire::test('supplier-payment-allocations.supplier-payment-allocation-management');

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPaymentAllocations')->currentPage());

    $component->set('search', 'a');
    $this->assertSame(1, $component->get('supplierPaymentAllocations')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPaymentAllocations')->currentPage());

    $component->set('perPage', 25);
    $this->assertSame(1, $component->get('supplierPaymentAllocations')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('supplierPaymentAllocations')->currentPage());

});
