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

it('displays list of sale returns', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['quantity' => 20]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 50,
        'reason' => 'Defective',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 2,
        'sale_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    $this->get(route('sale-returns'))
        ->assertOk()
        ->assertSee($return->number)
        ->assertSee($sale->number)
        ->assertSee('50.00', false);
});

it('filters sale returns by search number', function () {
    $product = Product::factory()->create(['quantity' => 30]);

    $customer1 = Customer::factory()->create();
    $sale1 = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 5,
        'sale_id' => $sale1->id,
        'product_id' => $product->id,
    ]);

    $return1 = SaleReturn::factory()->create([
        'total_price' => 100,
        'reason' => 'Test 1',
        'cash_refund' => true,
        'sale_id' => $sale1->id,
        'user_id' => auth()->id(),
    ]);

    $customer2 = Customer::factory()->create();
    $sale2 = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer2->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 5,
        'sale_id' => $sale2->id,
        'product_id' => $product->id,
    ]);

    $return2 = SaleReturn::factory()->create([
        'total_price' => 100,
        'reason' => 'Test 2',
        'cash_refund' => false,
        'sale_id' => $sale2->id,
        'user_id' => auth()->id(),
    ]);

    Livewire::test('sale-returns.sale-return-management')
        ->set('search', $return1->number)
        ->assertSee($return1->number)
        ->assertDontSee($return2->number);
});

it('deletes a sale return and restores product inventory', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['quantity' => 3]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $return = SaleReturn::factory()->create([
        'total_price' => 50,
        'reason' => 'Defective',
        'cash_refund' => true,
        'sale_id' => $sale->id,
        'user_id' => auth()->id(),
    ]);

    SaleReturnItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 2,
        'sale_return_id' => $return->id,
        'product_id' => $product->id,
    ]);

    ProductMovement::create([
        'quantity' => 2,
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product->id,
    ]);

    $product->increment('quantity', 2);
    $product->refresh();
    $this->assertEquals(5.0, (float) $product->quantity);

    Livewire::test('sale-returns.sale-return-management')
        ->call('setDelete', $return->id)
        ->call('delete')
        ->assertHasNoErrors();

    $product->refresh();
    $this->assertEquals(3.0, (float) $product->quantity);
    $this->assertDatabaseMissing('sale_returns', ['id' => $return->id]);
    $this->assertDatabaseMissing('product_movements', [
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
    ]);
});
