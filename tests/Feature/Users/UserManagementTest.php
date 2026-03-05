<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

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
