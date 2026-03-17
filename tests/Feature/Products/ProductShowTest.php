<?php

use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('displays product details', function () {
    $product = Product::factory()->create(['name' => 'Test Filter Product']);

    Livewire::test('products.product-show', ['product' => $product])
        ->assertSee('Test Filter Product')
        ->assertSee($product->category->name);
});

it('calculates total purchased from movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => 30,
        'movable_type' => Purchase::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => 20,
        'movable_type' => Purchase::class,
        'movable_id' => 2,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    expect($component->get('totalPurchased'))->toBe(50);
});

it('calculates total sold from movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => -20,
        'movable_type' => Sale::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => -10,
        'movable_type' => Sale::class,
        'movable_id' => 2,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    expect($component->get('totalSold'))->toBe(30);
});

it('calculates total damaged from movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

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

    $component = Livewire::test('products.product-show', ['product' => $product]);

    expect($component->get('totalDamaged'))->toBe(5);
});

it('calculates total sale returns from movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => 10,
        'movable_type' => SaleReturn::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    expect($component->get('totalSaleReturns'))->toBe(10);
});

it('calculates total purchase returns from movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => -8,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    expect($component->get('totalPurchaseReturns'))->toBe(8);
});

it('calculates correct stock from all movement types', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => 100,
        'movable_type' => Purchase::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => -30,
        'movable_type' => Sale::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => -5,
        'movable_type' => DamagedProduct::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => 10,
        'movable_type' => SaleReturn::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => -5,
        'movable_type' => PurchaseReturn::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    // 100 (purchases) - 30 (sales) - 5 (damaged) + 10 (sale returns) - 5 (purchase returns) = 70
    expect($component->get('calculatedStock'))->toBe(70);
});

it('shows movement history', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    ProductMovement::create([
        'quantity' => 50,
        'movable_type' => Purchase::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => -10,
        'movable_type' => Sale::class,
        'movable_id' => 1,
        'product_id' => $product->id,
    ]);

    $component = Livewire::test('products.product-show', ['product' => $product]);

    $movements = $component->get('movements');

    expect($movements->count())->toBe(2);
});

it('paginates movements', function () {
    $product = Product::factory()->create(['quantity' => 50]);

    foreach (range(1, 15) as $i) {
        ProductMovement::create([
            'quantity' => $i,
            'movable_type' => Purchase::class,
            'movable_id' => $i,
            'product_id' => $product->id,
        ]);
    }

    $component = Livewire::test('products.product-show', ['product' => $product]);

    $movements = $component->get('movements');

    expect($movements->count())->toBe(10);
    expect($movements->total())->toBe(15);
});

it('returns correct movement type labels', function () {
    $product = Product::factory()->create();

    $component = new \App\Livewire\Products\ProductShow();
    $component->mount($product);

    expect($component->getMovementTypeLabel(Purchase::class))->toBe(__('keywords.purchase'));
    expect($component->getMovementTypeLabel(Sale::class))->toBe(__('keywords.sale'));
    expect($component->getMovementTypeLabel(DamagedProduct::class))->toBe(__('keywords.damaged_product'));
});
