<?php

declare(strict_types=1);

namespace App\Actions\CustomerPayments;

use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CreateCustomerPaymentAction
{
    public function execute(int $saleId, array $data): ?CustomerPayment
    {
        $sale = Sale::with('paymentAllocations')->findOrFail($saleId);
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return null;
        }

        $allocations = $this->calculateAllocations($sale, $amount);
        $totalAllocated = collect($allocations)->sum('amount');

        if ($totalAllocated <= 0) {
            return null;
        }

        return DB::transaction(function () use ($sale, $data, $allocations, $totalAllocated) {
            $payment = CustomerPayment::create([
                'amount' => $totalAllocated,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'note' => $data['note'] ?? null,
                'customer_id' => $sale->customer_id,
                'user_id' => auth()->id(),
            ]);

            foreach ($allocations as $allocation) {
                CustomerPaymentAllocation::create([
                    'amount' => $allocation['amount'],
                    'customer_payment_id' => $payment->id,
                    'sale_id' => $allocation['sale_id'],
                ]);
            }

            return $payment;
        });
    }

    private function calculateAllocations(Sale $sale, float $amount): array
    {
        $allocations = [];

        $maxPayable = $sale->remaining_amount;
        $payable = min($amount, $maxPayable);

        if ($payable > 0) {
            $allocations[] = [
                'sale_id' => $sale->id,
                'amount' => $payable,
            ];
        }

        return $allocations;
    }
}

