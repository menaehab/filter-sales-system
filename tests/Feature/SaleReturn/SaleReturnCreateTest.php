<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a sale return and increases inventory', function () {
    $customer = Customer::factory()->create();
    $product1 = Product::factory()->create(['cost_price' => 40, 'quantity' => 10]);
    $product2 = Product::factory()->create(['cost_price' => 25, 'quantity' => 5]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 230,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 3,
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 30,
        'cost_price' => 25,
        'quantity' => 4,
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
    ]);

    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', $sale->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '2')
        ->set('items.1.selected', true)
        ->set('items.1.return_quantity', '3')
        ->set('reason', 'Quality defect')
        ->set('cash_refund', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $return = SaleReturn::with('items')->sole();

    $this->assertEquals($sale->id, $return->sale_id);
    $this->assertEquals('Quality defect', $return->reason);
    $this->assertTrue((bool) $return->cash_refund);
    $this->assertEquals(190.0, (float) $return->total_price); // 2*50 + 3*30
    $this->assertEquals(2, $return->items->count());

    $product1->refresh();
    $product2->refresh();

    $this->assertEquals(12.0, (float) $product1->quantity); // 10 + 2
    $this->assertEquals(8.0, (float) $product2->quantity); // 5 + 3

    $this->assertDatabaseHas('sale_return_items', [
        'sale_return_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'sell_price' => '50.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => SaleReturn::class,
        'movable_id' => $return->id,
        'product_id' => $product1->id,
        'quantity' => '2.00',
    ]);
});

it('validates that at least one item is selected for return', function () {
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
        'quantity' => 1,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', $sale->number)
        ->call('save')
        ->assertHasErrors('items');

    $this->assertDatabaseCount('sale_returns', 0);
});

it('validates return quantity does not exceed sold quantity', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['quantity' => 5]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 100,
        'quantity' => 3,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', $sale->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '5')
        ->call('save')
        ->assertHasErrors('items.0.return_quantity');

    $this->assertDatabaseCount('sale_returns', 0);
});

it('allows creating a return without cash refund', function () {
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

    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', $sale->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '1')
        ->set('cash_refund', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $return = SaleReturn::sole();
    $this->assertFalse((bool) $return->cash_refund);
});

it('shows validation error when sale number is not found', function () {
    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', 'NOT-EXISTS-001')
        ->call('save')
        ->assertHasErrors(['sale_number'])
        ->assertHasErrors(['items']);

    $this->assertDatabaseCount('sale_returns', 0);
});

it('creates customer credit when return is saved without cash refund', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['cost_price' => 40, 'quantity' => 10]);

    $sale = Sale::factory()->create([
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::factory()->create([
        'sell_price' => 60,
        'cost_price' => 40,
        'quantity' => 5,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $payment = CustomerPayment::factory()->create([
        'customer_id' => $customer->id,
        'amount' => 300,
        'payment_method' => 'cash',
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::factory()->create([
        'customer_payment_id' => $payment->id,
        'sale_id' => $sale->id,
        'amount' => 300,
    ]);

    Livewire::test('sale-returns.sale-return-create')
        ->set('sale_number', $sale->number)
        ->set('items.0.selected', true)
        ->set('items.0.return_quantity', '2')
        ->set('cash_refund', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sale-returns'));

    $customer->refresh();
    $this->assertEquals(120.0, (float) $customer->available_credit);
    $this->assertEquals(-120.0, (float) $customer->balance);
});
