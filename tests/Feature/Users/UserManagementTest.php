<?php

use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
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

it('validates required fields when empty', function () {
    Livewire::test('users.user-management')
        ->call('create')
        ->assertHasErrors([
            'form.name',
            'form.email',
            'form.password',
        ]);
});
