<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('updates a sale and recalculates stock and movements', function () {
    $oldCustomer = Customer::factory()->create(['name' => 'Old Customer']);
    $newCustomer = Customer::factory()->create(['name' => 'New Customer']);

    $oldProduct = Product::factory()->create([
        'name' => 'Old Product',
        'cost_price' => 15,
        'quantity' => 7,
    ]);
    $newProduct = Product::factory()->create([
        'name' => 'New Product',
        'cost_price' => 25,
        'quantity' => 6,
    ]);

    $sale = Sale::create([
        'dealer_name' => 'Old Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 30,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $oldCustomer->id,
    ]);

    SaleItem::create([
        'sell_price' => 15,
        'cost_price' => 15,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $oldProduct->id,
    ]);

    $oldProduct->decrement('quantity', 2);

    ProductMovement::create([
        'quantity' => -2,
        'movable_type' => Sale::class,
        'movable_id' => $sale->id,
        'product_id' => $oldProduct->id,
    ]);

    $payment = CustomerPayment::create([
        'amount' => 30,
        'payment_method' => 'cash',
        'customer_id' => $oldCustomer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 30,
        'customer_payment_id' => $payment->id,
        'sale_id' => $sale->id,
    ]);

    Livewire::test('sales.sale-edit', ['sale' => $sale])
        ->set('customer_id', $newCustomer->id)
        ->set('dealer_name', 'Updated Dealer')
        ->set('payment_type', 'cash')
        ->set('items', [[
            'product_id' => (string) $newProduct->id,
            'product_name' => $newProduct->name,
            'sell_price' => '30',
            'cost_price' => '25',
            'quantity' => '4',
        ]])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales'));

    $sale->refresh()->load('paymentAllocations');
    $oldProduct->refresh();
    $newProduct->refresh();

    $this->assertEquals($newCustomer->id, $sale->customer_id);
    $this->assertEquals('Updated Dealer', $sale->dealer_name);
    $this->assertEquals(120.0, (float) $sale->total_price);
    $this->assertGreaterThan(0, $sale->paymentAllocations->count());
    $this->assertEquals(7.0, (float) $oldProduct->quantity);
    $this->assertEquals(2.0, (float) $newProduct->quantity);

    $this->assertDatabaseMissing('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $oldProduct->id,
    ]);

    $this->assertDatabaseHas('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $newProduct->id,
        'sell_price' => '30.00',
        'cost_price' => '25.00',
        'quantity' => '4.00',
    ]);

    $this->assertDatabaseCount('product_movements', 1);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => Sale::class,
        'movable_id' => $sale->id,
        'product_id' => $newProduct->id,
        'quantity' => '-4.00',
    ]);
});

it('validates edited sale data before updating', function () {
    $customer = Customer::factory()->create(['name' => 'Edit Customer']);
    $product = Product::factory()->create(['quantity' => 5]);

    $sale = Sale::create([
        'dealer_name' => 'Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 20,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    SaleItem::create([
        'sell_price' => 10,
        'cost_price' => 10,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    Livewire::test('sales.sale-edit', ['sale' => $sale])
        ->set('customer_id', null)
        ->set('payment_type', 'installment')
        ->set('down_payment', '')
        ->set('installment_months', '')
        ->set('items', [[
            'product_id' => '',
            'product_name' => '',
            'sell_price' => '',
            'cost_price' => '',
            'quantity' => '',
        ]])
        ->call('update')
        ->assertHasErrors([
            'customer_id' => 'required',
            'down_payment' => 'required_if',
            'installment_months' => 'required_if',
            'items.0.product_id' => 'required',
            'items.0.sell_price' => 'required',
            'items.0.quantity' => 'required',
        ]);
});

it('allows setting created_at on sale edit when user has manage_created_at permission', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 10,
    ]);

    $sale = Sale::create([
        'dealer_name' => 'Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
        'created_at' => '2025-01-01 08:00:00',
        'updated_at' => '2025-01-01 08:00:00',
    ]);

    SaleItem::create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $product->decrement('quantity', 2);

    Livewire::test('sales.sale-edit', ['sale' => $sale])
        ->assertSeeHtml('name="created_at"')
        ->set('items.0.quantity', '2')
        ->set('created_at', '2026-02-02T11:22')
        ->call('update')
        ->assertHasNoErrors();

    $sale->refresh();

    expect($sale->created_at->toDateTimeString())->toBe('2026-02-02 11:22:00');
});

it('ignores created_at on sale edit when user lacks manage_created_at permission', function () {
    $limitedUser = User::factory()->create();
    $limitedUser->givePermissionTo('manage_sales');
    $this->actingAs($limitedUser);

    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 10,
    ]);

    $sale = Sale::create([
        'dealer_name' => 'Dealer',
        'user_name' => $limitedUser->name,
        'total_price' => 100,
        'payment_type' => 'cash',
        'user_id' => $limitedUser->id,
        'customer_id' => $customer->id,
        'created_at' => '2025-03-05 10:00:00',
        'updated_at' => '2025-03-05 10:00:00',
    ]);

    SaleItem::create([
        'sell_price' => 50,
        'cost_price' => 40,
        'quantity' => 2,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $product->decrement('quantity', 2);

    Livewire::test('sales.sale-edit', ['sale' => $sale])
        ->assertDontSeeHtml('name="created_at"')
        ->set('items.0.quantity', '2')
        ->set('created_at', '2026-04-10T09:45')
        ->call('update')
        ->assertHasNoErrors();

    $sale->refresh();

    expect($sale->created_at->toDateTimeString())->toBe('2025-03-05 10:00:00');
});
