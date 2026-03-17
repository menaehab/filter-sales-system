<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

beforeEach(function () {
    actAsAdmin($this);
});

it('shows sale details including items and payment history', function () {
    $customer = Customer::create(['name' => 'Detail Customer']);
    $product = Product::factory()->create(['name' => 'Carbon Filter']);

    $sale = Sale::create([
        'dealer_name' => 'Counter Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 120,
        'payment_type' => 'installment',
        'installment_amount' => 50,
        'installment_months' => 2,
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    $initialPayment = CustomerPayment::create([
        'amount' => 20,
        'payment_method' => 'cash',
        'customer_id' => $customer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 20,
        'customer_payment_id' => $initialPayment->id,
        'sale_id' => $sale->id,
    ]);

    SaleItem::create([
        'sell_price' => 30,
        'cost_price' => 20,
        'quantity' => 4,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $payment = CustomerPayment::create([
        'amount' => 25,
        'payment_method' => 'bank_transfer',
        'note' => 'First transfer',
        'customer_id' => $customer->id,
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'amount' => 25,
        'customer_payment_id' => $payment->id,
        'sale_id' => $sale->id,
    ]);

    $this->get(route('sales.show', $sale))
        ->assertOk()
        ->assertSee('Detail Customer')
        ->assertSee('Counter Dealer')
        ->assertSee(auth()->user()->name)
        ->assertSee('Carbon Filter')
        ->assertSee('bank_transfer')
        ->assertSee('First transfer')
        ->assertSee('120.00', false)
        ->assertSee('45.00', false);
});
