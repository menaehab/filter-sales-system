<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;

beforeEach(function () {
    actAsAdmin($this);
});

it('shows purchase details including items and payment history', function () {
    $supplier = Supplier::factory()->create(['name' => 'Detail Supplier']);
    $product = Product::factory()->create(['name' => 'Carbon Filter']);

    $purchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => auth()->user()->name,
        'total_price' => 120,
        'payment_type' => 'installment',
        'down_payment' => 20,
        'installment_amount' => 50,
        'installment_months' => 2,
        'next_installment_date' => now()->addMonth(),
        'user_id' => auth()->id(),
        'supplier_id' => $supplier->id,
    ]);

    PurchaseItem::create([
        'product_name' => $product->name,
        'cost_price' => 30,
        'quantity' => 4,
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $payment = SupplierPayment::create([
        'amount' => 25,
        'payment_method' => 'bank_transfer',
        'note' => 'First transfer',
        'supplier_id' => $supplier->id,
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 25,
        'supplier_payment_id' => $payment->id,
        'purchase_id' => $purchase->id,
    ]);

    $this->get(route('purchases.show', $purchase))
        ->assertOk()
        ->assertSee('Detail Supplier')
        ->assertSee(auth()->user()->name)
        ->assertSee('Carbon Filter')
        ->assertSee('bank_transfer')
        ->assertSee('First transfer')
        ->assertSee('120.00', false)
        ->assertSee('45.00', false);
});
