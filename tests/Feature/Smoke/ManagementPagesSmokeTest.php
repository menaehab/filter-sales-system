<?php

it('redirects guests from management pages to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
    $this->get(route('categories'))->assertRedirect(route('login'));
    $this->get(route('products'))->assertRedirect(route('login'));
    $this->get(route('places'))->assertRedirect(route('login'));
    $this->get(route('users'))->assertRedirect(route('login'));
    $this->get(route('suppliers'))->assertRedirect(route('login'));
    $this->get(route('customers'))->assertRedirect(route('login'));
    $this->get(route('purchases'))->assertRedirect(route('login'));
    $this->get(route('supplier-payments'))->assertRedirect(route('login'));
    $this->get(route('purchase-returns'))->assertRedirect(route('login'));
    $this->get(route('purchases.create'))->assertRedirect(route('login'));
});

it('allows authenticated users to open management pages', function () {
    actAsAdmin($this);

    $this->get(route('home'))->assertOk();
    $this->get(route('categories'))->assertOk();
    $this->get(route('products'))->assertOk();
    $this->get(route('places'))->assertOk();
    $this->get(route('users'))->assertOk();
    $this->get(route('suppliers'))->assertOk();
    $this->get(route('customers'))->assertOk();
    $this->get(route('purchases'))->assertOk();
    $this->get(route('supplier-payments'))->assertOk();
    $this->get(route('purchase-returns'))->assertOk();
    $this->get(route('purchases.create'))->assertOk();
});
