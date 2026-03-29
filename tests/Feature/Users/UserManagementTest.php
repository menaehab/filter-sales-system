<?php

use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a user with email only', function () {
    Livewire::test('users.user-management')
        ->set('form.name', 'John Doe')
        ->set('form.email', 'johnd@example.com')
        ->set('form.phone', '')
        ->set('form.password', 'secret123')
        ->set('form.password_confirmation', 'secret123')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-user');

    $this->assertDatabaseHas('users', [
        'email' => 'johnd@example.com',
    ]);

    $this->assertNull(User::where('email', 'johnd@example.com')->first()->phone);
});

it('allows optional phone', function () {
    $user = User::factory()->create();
    Livewire::test('users.user-management')
        ->set('form.name', 'Jane Doe')
        ->set('form.email', 'janed@example.com')
        ->set('form.phone', '')
        ->set('form.password', 'secret123')
        ->set('form.password_confirmation', 'secret123')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'janed@example.com']);
});

it('can assign permissions when creating a user', function () {
    // prepare a couple of permissions (avoid duplicates)
    Permission::firstOrCreate(['name' => 'manage_users']);
    Permission::firstOrCreate(['name' => 'manage_products']);

    Livewire::test('users.user-management')
        ->set('form.name', 'Perm User')
        ->set('form.email', 'perm@example.com')
        ->set('form.phone', '')
        ->set('form.password', 'secret123')
        ->set('form.password_confirmation', 'secret123')
        ->set('form.permissions', ['manage_users', 'manage_products'])
        ->call('create')
        ->assertHasNoErrors();

    $user = User::where('email', 'perm@example.com')->first();
    $this->assertTrue($user->hasPermissionTo('manage_users'));
    $this->assertTrue($user->hasPermissionTo('manage_products'));
});

it('can assign role and places when creating a user', function () {
    $firstPlace = Place::factory()->create();
    $secondPlace = Place::factory()->create();

    Livewire::test('users.user-management')
        ->set('form.name', 'Role User')
        ->set('form.role', 'manager')
        ->set('form.email', 'role.user@example.com')
        ->set('form.phone', '')
        ->set('form.password', 'secret123')
        ->set('form.password_confirmation', 'secret123')
        ->set('form.place_ids', [(string) $firstPlace->id, (string) $secondPlace->id])
        ->call('create')
        ->assertHasNoErrors();

    $user = User::where('email', 'role.user@example.com')->first();

    expect($user->role)->toBe('manager');
    $actualPlaceIds = $user->places()->pluck('places.id')->all();
    $expectedPlaceIds = [$firstPlace->id, $secondPlace->id];
    sort($actualPlaceIds);
    sort($expectedPlaceIds);

    expect($actualPlaceIds)->toBe($expectedPlaceIds);
});

it('validates required fields when empty', function () {
    Livewire::test('users.user-management')
        ->call('create')
        ->assertHasErrors([
            'form.name',
            'form.email',
            'form.password',
        ]);
});

it('syncs permissions when updating a user', function () {
    Permission::firstOrCreate(['name' => 'manage_users']);
    Permission::firstOrCreate(['name' => 'manage_products']);

    $existing = User::factory()->create();
    $existing->givePermissionTo('manage_users');

    Livewire::test('users.user-management')
        ->call('openEdit', $existing->id)
        ->set('form.permissions', ['manage_products'])
        ->call('updateUser')
        ->assertHasNoErrors();

    $existing->refresh();
    $this->assertFalse($existing->hasPermissionTo('manage_users'));
    $this->assertTrue($existing->hasPermissionTo('manage_products'));
});

it('updates role and syncs places when editing a user', function () {
    $firstPlace = Place::factory()->create();
    $secondPlace = Place::factory()->create();

    $existing = User::factory()->create(['role' => 'cashier']);
    $existing->places()->sync([$firstPlace->id]);

    Livewire::test('users.user-management')
        ->call('openEdit', $existing->id)
        ->set('form.role', 'admin')
        ->set('form.place_ids', [(string) $secondPlace->id])
        ->call('updateUser')
        ->assertHasNoErrors();

    $existing->refresh();

    expect($existing->role)->toBe('admin');
    expect($existing->places()->pluck('places.id')->all())->toBe([$secondPlace->id]);
});
