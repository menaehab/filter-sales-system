<?php

declare(strict_types=1);

namespace App\Actions\SupplierPayments;

use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class CreateSupplierPaymentAction
{
    public function execute(int $purchaseId, array $data): ?SupplierPayment
    {
        $purchase = Purchase::with('paymentAllocations')->findOrFail($purchaseId);
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return null;
        }

        $allocations = $this->calculateAllocations($purchase, $amount);
        $totalAllocated = collect($allocations)->sum('amount');

        if ($totalAllocated <= 0) {
            return null;
        }

        return DB::transaction(function () use ($purchase, $data, $allocations, $totalAllocated) {
            $payment = SupplierPayment::create([
                'amount' => $totalAllocated,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'note' => $data['note'] ?? null,
                'supplier_id' => $purchase->supplier_id,
                'user_id' => auth()->id(),
                'created_at' => $this->resolveCreatedAt(data_get($data, 'created_at')),
            ]);

            foreach ($allocations as $allocation) {
                SupplierPaymentAllocation::create([
                    'amount' => $allocation['amount'],
                    'supplier_payment_id' => $payment->id,
                    'purchase_id' => $allocation['purchase_id'],
                ]);
            }

            return $payment;
        });
    }

    private function calculateAllocations(Purchase $purchase, float $amount): array
    {
        $allocations = [];

        $maxPayable = $purchase->remaining_amount;
        $payable = min($amount, $maxPayable);

        if ($payable > 0) {
            $allocations[] = [
                'purchase_id' => $purchase->id,
                'amount' => $payable,
            ];
        }

        return $allocations;
    }

    private function resolveCreatedAt(mixed $createdAt): Carbon
    {
        if (auth()->user()?->can('manage_created_at') && filled($createdAt)) {
            return Carbon::parse((string) $createdAt);
        }

        return Carbon::now();
    }
}


