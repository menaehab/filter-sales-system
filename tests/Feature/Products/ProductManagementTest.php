<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a product', function () {
    $category = Category::factory()->create();

    Livewire::test('products.product-management')
        ->set('form.name', 'Laptop')
        ->set('form.cost_price', 1500)
        ->set('form.quantity', 5)
        ->set('form.description', 'Gaming laptop')
        ->set('form.category_id', $category->id)
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-product');

    $this->assertDatabaseHas('products', [
        'name' => 'Laptop',
        'category_id' => $category->id,
    ]);
});

it('validates required product fields', function () {
    Livewire::test('products.product-management')
        ->set('form.name', '')
        ->set('form.cost_price', null)
        ->set('form.quantity', null)
        ->set('form.category_id', null)
        ->call('create')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.cost_price' => 'required',
            'form.quantity' => 'required',
            'form.category_id' => 'required',
        ]);
});

it('validates category existence when creating a product', function () {
    Livewire::test('products.product-management')
        ->set('form.name', 'Mouse')
        ->set('form.cost_price', 100)
        ->set('form.quantity', 10)
        ->set('form.category_id', 99999)
        ->call('create')
        ->assertHasErrors(['form.category_id' => 'exists']);
});

it('updates a product', function () {
    $oldCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Old Product',
        'category_id' => $oldCategory->id,
        'cost_price' => 100,
        'quantity' => 1,
    ]);

    Livewire::test('products.product-management')
        ->call('openEdit', $product->id)
        ->set('form.name', 'Updated Product')
        ->set('form.cost_price', 250)
        ->set('form.quantity', 3)
        ->set('form.description', 'Updated description')
        ->set('form.category_id', $newCategory->id)
        ->call('updateProduct')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-product');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
        'category_id' => $newCategory->id,
        'quantity' => 3,
    ]);
});

it('deletes a product', function () {
    $product = Product::factory()->create();

    Livewire::test('products.product-management')
        ->call('setDelete', $product->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-product');

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

it('filters products by search term', function () {
    $category = Category::factory()->create();

    Product::factory()->create([
        'name' => 'Gaming Laptop',
        'category_id' => $category->id,
    ]);
    Product::factory()->create([
        'name' => 'Office Chair',
        'category_id' => $category->id,
    ]);

    $component = Livewire::test('products.product-management')
        ->set('search', 'Gaming');

    $products = $component->get('products');

    $this->assertSame(1, $products->total());
    $this->assertSame(['Gaming Laptop'], collect($products->items())->pluck('name')->all());
});

it('filters products by category slug', function () {
    $electronics = Category::factory()->create(['name' => 'Electronics']);
    $furniture = Category::factory()->create(['name' => 'Furniture']);

    Product::factory()->create([
        'name' => 'Keyboard',
        'category_id' => $electronics->id,
    ]);
    Product::factory()->create([
        'name' => 'Desk',
        'category_id' => $furniture->id,
    ]);

    $component = Livewire::test('products.product-management')
        ->set('categorySlug', $electronics->slug);

    $products = $component->get('products');

    $this->assertSame(1, $products->total());
    $this->assertSame(['Keyboard'], collect($products->items())->pluck('name')->all());
});

it('paginates products using per page selection', function () {
    Product::factory()->count(15)->create();

    $component = Livewire::test('products.product-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('products'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('products'));
});

it('resets page when search, per page, or category filter changes', function () {
    Product::factory()->count(30)->create();

    $component = Livewire::test('products.product-management');

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('products')->currentPage());

    $component->set('search', 'a');
    $this->assertSame(1, $component->get('products')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('products')->currentPage());

    $component->set('perPage', 25);
    $this->assertSame(1, $component->get('products')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('products')->currentPage());

    $component->set('categorySlug', 'non-existing-slug');
    $this->assertSame(1, $component->get('products')->currentPage());
});
