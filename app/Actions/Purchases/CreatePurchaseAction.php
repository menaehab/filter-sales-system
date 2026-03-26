<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseAction
{
    public function execute(array $data): Purchase
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);
        $totalPrice = $this->calculateTotalPrice($data['items']);

        $isInstallment = ($data['payment_type'] ?? 'cash') === 'installment';
        $appliedCredit = min($supplier->available_credit, $totalPrice);
        $downPayment = $isInstallment
            ? min((float) ($data['down_payment'] ?? 0), max(0, $totalPrice - $appliedCredit))
            : max(0, $totalPrice - $appliedCredit);
        $months = $isInstallment ? (int) ($data['installment_months'] ?? 0) : null;
        $installmentAmount = $isInstallment && $months > 0
            ? round(max(0, $totalPrice - $appliedCredit - $downPayment) / $months, 2)
            : null;

        return DB::transaction(function () use (
            $data,
            $supplier,
            $totalPrice,
            $isInstallment,
            $downPayment,
            $months,
            $installmentAmount,
            $appliedCredit
        ) {
            $purchase = Purchase::create([
                'supplier_name' => $supplier->name,
                'user_name' => auth()->user()->name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'user_id' => auth()->id(),
                'supplier_id' => $supplier->id,
            ]);

            $this->createPurchaseItems($purchase, $data['items']);
            $this->createPayments($purchase, $supplier, $downPayment, $appliedCredit, $isInstallment);

            return $purchase;
        });
    }

    private function calculateTotalPrice(array $items): float
    {
        return collect($items)->sum(function ($item) {
            return ((float) ($item['cost_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });
    }

    private function createPurchaseItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            PurchaseItem::create([
                'product_name' => $product->name,
                'cost_price' => (float) $item['cost_price'],
                'quantity' => (int) $item['quantity'],
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
            ]);

            $product->update([
                'cost_price' => (float) $item['cost_price'],
            ]);

            $product->increment('quantity', (int) $item['quantity']);

            ProductMovement::create([
                'quantity' => (int) $item['quantity'],
                'movable_type' => Purchase::class,
                'movable_id' => $purchase->id,
                'product_id' => $product->id,
            ]);
        }
    }

    private function createPayments(
        Purchase $purchase,
        Supplier $supplier,
        float $downPayment,
        float $appliedCredit,
        bool $isInstallment
    ): void {
        if ($downPayment > 0) {
            $payment = SupplierPayment::create([
                'amount' => $downPayment,
                'payment_method' => 'cash',
                'note' => $isInstallment ? __('keywords.down_payment') : __('keywords.cash_payment'),
                'supplier_id' => $supplier->id,
                'user_id' => auth()->id(),
            ]);

            SupplierPaymentAllocation::create([
                'amount' => $downPayment,
                'supplier_payment_id' => $payment->id,
                'purchase_id' => $purchase->id,
            ]);
        }

        if ($appliedCredit > 0) {
            $creditPayment = SupplierPayment::create([
                'amount' => $appliedCredit,
                'payment_method' => 'supplier_credit',
                'note' => __('keywords.applied_supplier_credit'),
                'supplier_id' => $supplier->id,
                'user_id' => auth()->id(),
            ]);

            SupplierPaymentAllocation::create([
                'amount' => $appliedCredit,
                'supplier_payment_id' => $creditPayment->id,
                'purchase_id' => $purchase->id,
            ]);
        }
    }
}
