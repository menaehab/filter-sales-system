<?php

use App\Models\Supplier;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a supplier', function () {
    Livewire::test('suppliers.supplier-management')
        ->set('form.name', 'John Doe')
        ->set('form.phone', '01234567890')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-supplier');

    $this->assertDatabaseHas('suppliers', [
        'name' => 'John Doe',
        'phone' => '01234567890',
    ]);
});

it('validates supplier data when creating', function () {
    Livewire::test('suppliers.supplier-management')
        ->set('form.name', '')
        ->call('create')
        ->assertHasErrors(['form.name' => 'required']);
});

it('updates a supplier', function () {
    $supplier = Supplier::factory()->create(['name' => 'Old Name', 'phone' => '01234567890']);

    Livewire::test('suppliers.supplier-management')
        ->call('openEdit', $supplier->id)
        ->set('form.name', 'New Name')
        ->set('form.phone', '01576543210')
        ->call('updateSupplier')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-supplier');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'New Name',
        'phone' => '01576543210',
    ]);
});

it('deletes a supplier', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test('suppliers.supplier-management')
        ->call('setDelete', $supplier->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-supplier');

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id,
    ]);
});

it('filters suppliers by search term', function () {
    Supplier::factory()->create(['name' => 'John Doe', 'phone' => '01234567890']);
    Supplier::factory()->create(['name' => 'Jane Smith', 'phone' => '09876543210']);

    $component = Livewire::test('suppliers.supplier-management')
        ->set('search', 'John');

    $suppliers = $component->get('suppliers');

    $this->assertSame(1, $suppliers->total());
    $this->assertSame(['John Doe'], collect($suppliers->items())->pluck('name')->all());
});

it('paginates suppliers using per page selection', function () {
    Supplier::factory()->count(15)->create();

    $component = Livewire::test('suppliers.supplier-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('suppliers'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('suppliers'));
});

it('show supplier details page', function () {
    $supplier = Supplier::factory()->create(['name' => 'John Doe', 'phone' => '01234567890']);

    Livewire::test('suppliers.supplier-details', ['supplier' => $supplier])
        ->assertSet('supplier.name', 'John Doe')
        ->assertSet('supplier.phone', '01234567890');
});
