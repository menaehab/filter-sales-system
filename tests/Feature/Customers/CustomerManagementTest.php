<?php

use App\Models\Customer;
use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a customer with selected place', function () {
    $place = Place::factory()->create(['name' => 'Nasr City']);

    Livewire::test('customers.customer-management')
        ->set('form.name', 'Ahmed Ali')
        ->set('form.place_id', (string) $place->id)
        ->set('form.phones.0.number', '')
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

it('does not show customers in other places when user has view_only_customers_in_his_places permission', function () {
    (new \Database\Seeders\PermissionSeeder)->run();

    $placeA = Place::factory()->create(['name' => 'Place A']);
    $placeB = Place::factory()->create(['name' => 'Place B']);

    $user = User::factory()->create();
    $user->givePermissionTo('view_only_customers_in_his_places');
    $user->places()->attach($placeA->id);

    $this->actingAs($user);

    Customer::factory()->create(['name' => 'Alice', 'place_id' => $placeA->id]);
    Customer::factory()->create(['name' => 'Bob', 'place_id' => $placeB->id]);

    Livewire::test('customers.customer-management')
        ->assertSee('Alice')
        ->assertDontSee('Bob');
});
