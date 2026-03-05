<?php


it('redirects guests from management pages to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
    $this->get(route('categories'))->assertRedirect(route('login'));
    $this->get(route('products'))->assertRedirect(route('login'));
    $this->get(route('users'))->assertRedirect(route('login'));
});

it('allows authenticated users to open management pages', function () {
    actAsAdmin($this);

    $this->get(route('home'))->assertOk();
    $this->get(route('categories'))->assertOk();
    $this->get(route('products'))->assertOk();
    $this->get(route('users'))->assertOk();
});
