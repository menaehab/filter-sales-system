<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'dashboard')->name('home');
    Route::livewire('/categories','categories.category-management')->name('categories')->middleware('permission:manage_categories');
    Route::livewire('/products','products.product-management')->name('products')->middleware('permission:view_products|manage_products');


    Route::livewire('/users','users.user-management')->name('users')->middleware('permission:manage_users');



    Route::livewire('/suppliers','suppliers.supplier-management')->name('suppliers')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/suppliers/{supplier:slug}', 'suppliers.supplier-details')->name('suppliers.show')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/customers','customers.customer-management')->name('customers')->middleware('permission:view_customers|manage_customers');
    Route::livewire('/customers/{customer:slug}', 'customers.customer-details')->name('customers.show')->middleware('permission:view_customers|manage_customers');

    Route::livewire('/purchases','purchases.purchase-management')->name('purchases')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchases/create','purchases.purchase-create')->name('purchases.create')->middleware('permission:manage_purchases');
    Route::livewire('/purchases/{purchase}','purchases.purchase-show')->name('purchases.show')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchases/{purchase}/edit','purchases.purchase-edit')->name('purchases.edit')->middleware('permission:manage_purchases');
    Route::livewire('/supplier-payment-allocations','supplier-payment-allocations.supplier-payment-allocations-management')->name('supplier-payment-allocations')->middleware('permission:view_supplier_payment_allocations|manage_supplier_payment_allocations');
});
