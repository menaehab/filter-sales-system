<?php

use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a damaged product and deducts stock', function () {
    $product = Product::factory()->create(['quantity' => 100, 'cost_price' => 50.00]);

    Livewire::test('damaged-products.damaged-product-management')
        ->set('form.product_id', $product->id)
        ->set('form.quantity', 5)
        ->set('form.reason', 'Broken during transport')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-damaged-product');

    $this->assertDatabaseHas('damaged_products', [
        'product_id' => $product->id,
        'quantity' => 5,
        'cost_price' => '50.00',
        'reason' => 'Broken during transport',
    ]);

    expect($product->fresh()->quantity)->toBe(95);

    $this->assertDatabaseHas('product_movements', [
        'product_id' => $product->id,
        'quantity' => -5,
        'movable_type' => DamagedProduct::class,
    ]);
});

it('validates required damaged product fields', function () {
    Livewire::test('damaged-products.damaged-product-management')
        ->set('form.product_id', null)
        ->set('form.quantity', null)
        ->call('create')
        ->assertHasErrors([
            'form.product_id' => 'required',
            'form.quantity' => 'required',
        ]);
});

it('validates quantity does not exceed available stock', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    Livewire::test('damaged-products.damaged-product-management')
        ->set('form.product_id', $product->id)
        ->set('form.quantity', 15)
        ->set('form.reason', 'Test')
        ->call('create')
        ->assertHasErrors(['form.quantity']);
});

it('validates product existence', function () {
    Livewire::test('damaged-products.damaged-product-management')
        ->set('form.product_id', 99999)
        ->set('form.quantity', 5)
        ->call('create')
        ->assertHasErrors(['form.product_id' => 'exists']);
});

it('updates a damaged product and adjusts stock', function () {
    $product = Product::factory()->create(['quantity' => 95, 'cost_price' => 50.00]);

    $damage = DamagedProduct::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'cost_price' => 50.00,
        'reason' => 'Old reason',
        'user_id' => auth()->id(),
    ]);

    ProductMovement::create([
        'quantity' => -5,
        'movable_type' => DamagedProduct::class,
        'movable_id' => $damage->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('damaged-products.damaged-product-management')
        ->call('openEdit', $damage->id)
        ->set('form.quantity', 10)
        ->set('form.reason', 'Updated reason')
        ->call('updateDamagedProduct')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-damaged-product');

    $this->assertDatabaseHas('damaged_products', [
        'id' => $damage->id,
        'quantity' => 10,
        'reason' => 'Updated reason',
    ]);

    expect($product->fresh()->quantity)->toBe(90);
});

it('deletes a damaged product and restores stock', function () {
    $product = Product::factory()->create(['quantity' => 95]);

    $damage = DamagedProduct::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'user_id' => auth()->id(),
    ]);

    ProductMovement::create([
        'quantity' => -5,
        'movable_type' => DamagedProduct::class,
        'movable_id' => $damage->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('damaged-products.damaged-product-management')
        ->call('setDelete', $damage->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-damaged-product');

    $this->assertDatabaseMissing('damaged_products', ['id' => $damage->id]);
    expect($product->fresh()->quantity)->toBe(100);
});

it('filters damaged products by search term', function () {
    $product1 = Product::factory()->create(['name' => 'Filter A']);
    $product2 = Product::factory()->create(['name' => 'Filter B']);

    DamagedProduct::factory()->create([
        'product_id' => $product1->id,
        'reason' => 'Broken handle',
        'user_id' => auth()->id(),
    ]);

    DamagedProduct::factory()->create([
        'product_id' => $product2->id,
        'reason' => 'Manufacturing defect',
        'user_id' => auth()->id(),
    ]);

    $component = Livewire::test('damaged-products.damaged-product-management')
        ->set('search', 'Broken');

    $damages = $component->get('damagedProducts');

    $this->assertSame(1, $damages->total());
});

it('filters damaged products by product slug', function () {
    $product1 = Product::factory()->create(['name' => 'Product One']);
    $product2 = Product::factory()->create(['name' => 'Product Two']);

    DamagedProduct::factory()->create([
        'product_id' => $product1->id,
        'user_id' => auth()->id(),
    ]);

    DamagedProduct::factory()->create([
        'product_id' => $product2->id,
        'user_id' => auth()->id(),
    ]);

    $component = Livewire::test('damaged-products.damaged-product-management')
        ->set('productSlug', $product1->slug);

    $damages = $component->get('damagedProducts');

    $this->assertSame(1, $damages->total());
});

it('paginates damaged products', function () {
    $product = Product::factory()->create();

    foreach (range(1, 15) as $i) {
        DamagedProduct::factory()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'user_id' => auth()->id(),
        ]);
    }

    $component = Livewire::test('damaged-products.damaged-product-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('damagedProducts'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('damagedProducts'));
});
