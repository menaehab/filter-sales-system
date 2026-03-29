<?php

use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a place and syncs selected users', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    Livewire::test('places.place-management')
        ->set('form.name', 'Downtown Branch')
        ->set('form.user_ids', [(string) $firstUser->id, (string) $secondUser->id])
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-place');

    $place = Place::where('name', 'Downtown Branch')->first();

    expect($place)->not->toBeNull();

    $this->assertDatabaseHas('place_user', [
        'place_id' => $place->id,
        'user_id' => $firstUser->id,
    ]);

    $this->assertDatabaseHas('place_user', [
        'place_id' => $place->id,
        'user_id' => $secondUser->id,
    ]);
});

it('validates place data when creating', function () {
    Livewire::test('places.place-management')
        ->set('form.name', '')
        ->call('create')
        ->assertHasErrors(['form.name' => 'required']);
});

it('prevents duplicate place names when creating', function () {
    Place::factory()->create(['name' => 'Main Branch']);

    Livewire::test('places.place-management')
        ->set('form.name', 'Main Branch')
        ->call('create')
        ->assertHasErrors(['form.name' => 'unique']);
});

it('updates a place and syncs selected users', function () {
    $place = Place::factory()->create(['name' => 'Old Place']);
    $oldUser = User::factory()->create();
    $newUser = User::factory()->create();

    $place->users()->sync([$oldUser->id]);

    Livewire::test('places.place-management')
        ->call('openEdit', $place->id)
        ->set('form.name', 'New Place')
        ->set('form.user_ids', [(string) $newUser->id])
        ->call('updatePlace')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-place');

    $place->refresh();

    expect($place->name)->toBe('New Place');
    expect($place->users()->pluck('users.id')->all())->toBe([$newUser->id]);
});

it('allows keeping the same name while updating the same place', function () {
    $place = Place::factory()->create(['name' => 'Warehouse']);

    Livewire::test('places.place-management')
        ->call('openEdit', $place->id)
        ->set('form.name', 'Warehouse')
        ->call('updatePlace')
        ->assertHasNoErrors();
});

it('prevents duplicate place names when updating another place', function () {
    $first = Place::factory()->create(['name' => 'Branch A']);
    $second = Place::factory()->create(['name' => 'Branch B']);

    Livewire::test('places.place-management')
        ->call('openEdit', $second->id)
        ->set('form.name', $first->name)
        ->call('updatePlace')
        ->assertHasErrors(['form.name' => 'unique']);
});

it('deletes a place', function () {
    $place = Place::factory()->create();

    Livewire::test('places.place-management')
        ->call('setDelete', $place->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-place');

    $this->assertDatabaseMissing('places', [
        'id' => $place->id,
    ]);
});

it('filters places by search term', function () {
    Place::factory()->create(['name' => 'Alexandria']);
    Place::factory()->create(['name' => 'Cairo']);

    $component = Livewire::test('places.place-management')
        ->set('search', 'Alex');

    $places = $component->get('places');

    expect($places->total())->toBe(1);
    expect(collect($places->items())->pluck('name')->all())->toBe(['Alexandria']);
});

it('paginates places using per page selection', function () {
    Place::factory()->count(15)->create();

    $component = Livewire::test('places.place-management')
        ->set('perPage', 10);

    expect($component->get('places'))->toHaveCount(10);

    $component->call('setPage', 2);

    expect($component->get('places'))->toHaveCount(5);
});
