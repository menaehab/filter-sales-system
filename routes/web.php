<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'dashboard')->name('home');
    Route::livewire('/categories','categories.category-management')->name('categories')->middleware('permission:manage_categories');
    Route::livewire('/products','products.product-management')->name('products')->middleware('permission:view_products|manage_products');


    Route::livewire('/users','users.user-management')->name('users')->middleware('permission:manage_users');



    Route::livewire('/suppliers','suppliers.supplier-management')->name('suppliers')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/suppliers/{supplier:slug}', 'suppliers.supplier-details')->name('suppliers.show')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/suppliers/{supplier:slug}/view', 'suppliers.supplier-view')->name('suppliers.view')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/customers','customers.customer-management')->name('customers')->middleware('permission:view_customers|manage_customers');
    Route::livewire('/customers/{customer:slug}', 'customers.customer-details')->name('customers.show')->middleware('permission:view_customers|manage_customers');

    Route::livewire('/purchases','purchases.purchase-management')->name('purchases')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchases/create','purchases.purchase-create')->name('purchases.create')->middleware('permission:manage_purchases');
    Route::livewire('/purchases/{purchase}','purchases.purchase-show')->name('purchases.show')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchases/{purchase}/edit','purchases.purchase-edit')->name('purchases.edit')->middleware('permission:manage_purchases');
    Route::livewire('/supplier-payments','supplier-payments.supplier-payment-management')->name('supplier-payments')->middleware('permission:view_supplier_payment_allocations|manage_supplier_payment_allocations');

    Route::livewire('/purchase-returns','purchase-returns.purchase-return-management')->name('purchase-returns')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchase-returns/create','purchase-returns.purchase-return-create')->name('purchase-returns.create')->middleware('permission:manage_purchases');
    Route::livewire('/purchase-returns/{purchaseReturn}','purchase-returns.purchase-return-show')->name('purchase-returns.show')->middleware('permission:view_purchases|manage_purchases');
    Route::livewire('/purchase-returns/{purchaseReturn}/edit','purchase-returns.purchase-return-edit')->name('purchase-returns.edit')->middleware('permission:manage_purchases');
});
