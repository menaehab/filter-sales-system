<?php

use App\Models\Customer;
use App\Models\Place;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a customer with selected place', function () {
    $place = Place::factory()->create(['name' => 'Nasr City']);

    Livewire::test('customers.customer-management')
        ->set('form.name', 'Ahmed Ali')
        ->set('form.place_id', (string) $place->id)
        ->set('form.phone', '')
        ->set('form.national_number', '')
        ->set('form.address', 'Test Address')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-customer');

    $this->assertDatabaseHas('customers', [
        'name' => 'Ahmed Ali',
        'place_id' => $place->id,
    ]);
});

it('validates place as required when creating customer', function () {
    Livewire::test('customers.customer-management')
        ->set('form.name', 'No Place Customer')
        ->set('form.place_id', '')
        ->call('create')
        ->assertHasErrors(['form.place_id' => 'required']);
});

it('updates customer place', function () {
    $oldPlace = Place::factory()->create(['name' => 'Heliopolis']);
    $newPlace = Place::factory()->create(['name' => 'Maadi']);

    $customer = Customer::factory()->create([
        'name' => 'Customer One',
        'place_id' => $oldPlace->id,
        'phone' => null,
        'national_number' => null,
    ]);

    Livewire::test('customers.customer-management')
        ->call('openEdit', $customer->id)
        ->set('form.place_id', (string) $newPlace->id)
        ->call('updateCustomer')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-customer');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'place_id' => $newPlace->id,
    ]);
});
