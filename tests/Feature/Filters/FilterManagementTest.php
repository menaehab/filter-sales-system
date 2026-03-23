<?php

use App\Models\Customer;
use App\Models\WaterFilter;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a water filter', function () {
    $customer = Customer::factory()->create();

    Livewire::test('filters.filter-management')
        ->set('form.filter_model', 'Model X-2000')
        ->set('form.address', '123 Main Street')
        ->set('form.customer_id', $customer->id)
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-filter');

    $this->assertDatabaseHas('water_filters', [
        'filter_model' => 'Model X-2000',
        'address' => '123 Main Street',
        'customer_id' => $customer->id,
    ]);
});

it('validates required filter fields', function () {
    Livewire::test('filters.filter-management')
        ->set('form.filter_model', '')
        ->set('form.address', '')
        ->set('form.customer_id', null)
        ->call('create')
        ->assertHasErrors([
            'form.filter_model' => 'required',
            'form.address' => 'required',
            'form.customer_id' => 'required',
        ]);
});

it('validates customer existence when creating a filter', function () {
    Livewire::test('filters.filter-management')
        ->set('form.filter_model', 'Model ABC')
        ->set('form.address', 'Test Address')
        ->set('form.customer_id', 99999)
        ->call('create')
        ->assertHasErrors(['form.customer_id' => 'exists']);
});

it('updates a water filter', function () {
    $oldCustomer = Customer::factory()->create();
    $newCustomer = Customer::factory()->create();

    $filter = WaterFilter::create([
        'filter_model' => 'Old Model',
        'address' => 'Old Address',
        'customer_id' => $oldCustomer->id,
    ]);

    Livewire::test('filters.filter-management')
        ->call('openEdit', $filter->id)
        ->set('form.filter_model', 'New Model')
        ->set('form.address', 'New Address')
        ->set('form.customer_id', $newCustomer->id)
        ->call('updateFilter')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-filter');

    $this->assertDatabaseHas('water_filters', [
        'id' => $filter->id,
        'filter_model' => 'New Model',
        'address' => 'New Address',
        'customer_id' => $newCustomer->id,
    ]);
});

it('deletes a water filter', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Delete Model',
        'address' => 'Delete Address',
        'customer_id' => $customer->id,
    ]);

    Livewire::test('filters.filter-management')
        ->call('setDelete', $filter->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-filter');

    $this->assertDatabaseMissing('water_filters', [
        'id' => $filter->id,
    ]);
});

it('filters by search term', function () {
    $customer = Customer::factory()->create();

    WaterFilter::create([
        'filter_model' => 'Searchable Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
    ]);

    WaterFilter::create([
        'filter_model' => 'Other Model',
        'address' => 'Other Address',
        'customer_id' => $customer->id,
    ]);

    $component = Livewire::test('filters.filter-management')
        ->set('search', 'Searchable');

    $filters = $component->get('filters');

    $this->assertSame(1, $filters->total());
    $this->assertSame(['Searchable Model'], collect($filters->items())->pluck('filter_model')->all());
});

it('filters by customer', function () {
    $firstCustomer = Customer::factory()->create(['name' => 'First Customer']);
    $secondCustomer = Customer::factory()->create(['name' => 'Second Customer']);

    WaterFilter::create([
        'filter_model' => 'Model 1',
        'address' => 'Address 1',
        'customer_id' => $firstCustomer->id,
    ]);

    WaterFilter::create([
        'filter_model' => 'Model 2',
        'address' => 'Address 2',
        'customer_id' => $secondCustomer->id,
    ]);

    $component = Livewire::test('filters.filter-management')
        ->set('customerSlug', $firstCustomer->slug);

    $filters = $component->get('filters');

    $this->assertSame(1, $filters->total());
    $this->assertSame(['Model 1'], collect($filters->items())->pluck('filter_model')->all());
});

it('paginates filters using per page selection', function () {
    $customer = Customer::factory()->create();

    foreach (range(1, 15) as $i) {
        WaterFilter::create([
            'filter_model' => "Model {$i}",
            'address' => "Address {$i}",
            'customer_id' => $customer->id,
        ]);
    }

    $component = Livewire::test('filters.filter-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('filters'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('filters'));
});
