<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::livewire('/categories','categories.category-management')->name('categories')->middleware('permission:manage_categories');
    Route::livewire('/products','products.product-management')->name('products')->middleware('permission:manage_products');
    Route::livewire('/users','users.user-management')->name('users')->middleware('permission:manage_users');
    Route::livewire('/suppliers','suppliers.supplier-management')->name('suppliers')->middleware('permission:view_suppliers|manage_suppliers');
    Route::livewire('/suppliers/{supplier:slug}', 'suppliers.supplier-details')->name('suppliers.show')->middleware('permission:view_suppliers|manage_suppliers');
});
