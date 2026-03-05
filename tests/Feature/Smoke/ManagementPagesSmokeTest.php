<?php

use App\Models\User;

it('redirects guests from management pages to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
    $this->get(route('categories'))->assertRedirect(route('login'));
    $this->get(route('products'))->assertRedirect(route('login'));
});

it('allows authenticated users to open management pages', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('home'))->assertOk();
    $this->get(route('categories'))->assertOk();
    $this->get(route('products'))->assertOk();
});
