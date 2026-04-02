<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('updates a sale return and recalculates stock', function () {
    $customer = Customer::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 10]);
    $product2 = Product::factory()->create(['quantity' => 15]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 500,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 30,
        'quantity' => 10,
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 200,
        'reason' => 'Defective',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 1,
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 30,
        'quantity' => 5,
        'sale_return_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    ProductMovement::create([
        'quantity' => 1,
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    ProductMovement::create([
        'quantity' => 5,
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product2->id,
    ]);

    $product1->increment('quantity', 1);
    $product2->increment('quantity', 5);

    $product1->refresh();
    $product2->refresh();
    $this->assertEquals(11.0, (float) $product1->quantity);
    $this->assertEquals(20.0, (float) $product2->quantity);

    Livewire::test('sale-returns.sale-return-edit', ['saleReturn' => $return])
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '3')
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '2')
        ->set('reason', 'Updated reason')
        ->set('cash_refund', false)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $return->refresh();
    $product1->refresh();
    $product2->refresh();

    $this->assertEquals('Updated reason', $return->reason);
    $this->assertFalse((bool) $return->cash_refund);
    $this->assertEquals(210.0, (float) $return->total_price); // 3*50 + 2*30
    $this->assertEquals(13.0, (float) $product1->quantity);
    $this->assertEquals(17.0, (float) $product2->quantity);

    $this->assertDatabaseHas('sale_return_items', [
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 3,
    ]);

    $this->assertDatabaseHas('sale_return_items', [
        'sale_return_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => 2,
    ]);
});

it('validates edited return data before updating', function () {
    $customer = Customer::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 10]);
    $product2 = Product::factory()->create(['quantity' => 10]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 200,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 1,
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 50,
        'reason' => null,
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 50,
        'quantity' => 1,
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    Livewire::test('sale-returns.sale-return-edit', ['saleReturn' => $return])
        ->set('items.0.selected', false)
        ->set('items.1.selected', false)
        ->call('update')
        ->assertHasErrors('items');

    $this->assertDatabaseCount('sale_return_items', 1);
});

it('allows updating return with no reason', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 100,
        'reason' => 'Old reason',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 1,
        'sale_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('sale-returns.sale-return-edit', ['saleReturn' => $return])
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '1')
        ->set('reason', '')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $return->refresh();
    $this->assertNull($return->reason);
});

it('replaces old return items and movements when product selection changes', function () {
    $customer = Customer::factory()->create();
    $product1 = Product::factory()->create(['quantity' => 20]);
    $product2 = Product::factory()->create(['quantity' => 20]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 400,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 40,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 60,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 80,
        'reason' => 'Initial',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 40,
        'quantity' => 2,
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    ProductMovement::create([
        'quantity' => 2,
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
    ]);

    $product1->increment('quantity', 2);

    Livewire::test('sale-returns.sale-return-edit', ['saleReturn' => $return])
        ->set('items.0.selected', false)
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '4')
        ->set('cash_refund', false)
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $return->refresh();
    $product1->refresh();
    $product2->refresh();

    $this->assertEquals(240.0, (float) $return->total_price);
    $this->assertFalse((bool) $return->cash_refund);
    $this->assertEquals(20.0, (float) $product1->quantity);
    $this->assertEquals(24.0, (float) $product2->quantity);

    $this->assertDatabaseMissing('sale_return_items', [
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('sale_return_items', [
        'sale_return_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => 4,
    ]);

    $this->assertDatabaseMissing('product_movements', [
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => '2.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product2->id,
        'quantity' => '4.00',
    ]);
});
