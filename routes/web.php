<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::livewire('/categories','categories.category-management')->name('categories');
    Route::livewire('/products','products.product-management')->name('products');
});
