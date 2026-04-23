<?php

declare(strict_types=1);

namespace App\Actions\SupplierPayments;

use App\Models\SupplierPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSupplierPaymentAction
{
    public function execute(SupplierPayment $payment, array $data): SupplierPayment
    {
        return DB::transaction(function () use ($payment, $data): SupplierPayment {
            $payment->load('allocations.purchase');

            $oldTotal = (float) $payment->allocations->sum('amount');
            $newTotal = round((float) $data['amount'], 2);

            $maxAllocatable = $this->maxAllocatableAmount($payment);

            if ($payment->allocations->isNotEmpty() && $newTotal > $maxAllocatable) {
                throw ValidationException::withMessages([
                    'form.amount' => __('validation.max.numeric', [
                        'attribute' => __('keywords.amount'),
                        'max' => number_format($maxAllocatable, 2, '.', ''),
                    ]),
                ]);
            }

            $payload = [
                'amount' => $newTotal,
                'payment_method' => $data['payment_method'],
                'note' => filled($data['note'] ?? null) ? $data['note'] : null,
            ];

            if (auth()->user()?->can('manage_created_at') && filled($data['created_at'] ?? null)) {
                $payload['created_at'] = Carbon::parse((string) $data['created_at']);
            }

            $payment->update($payload);

            $this->syncAllocations($payment, $oldTotal, $newTotal);

            return $payment->fresh();
        });
    }

    private function syncAllocations(SupplierPayment $payment, float $oldTotal, float $newTotal): void
    {
        $allocations = $payment->allocations;

        if ($allocations->isEmpty()) {
            return;
        }

        $remaining = $newTotal;
        $lastIndex = $allocations->count() - 1;

        foreach ($allocations as $index => $allocation) {
            if ($index === $lastIndex) {
                $amount = round($remaining, 2);
            } elseif ($oldTotal > 0) {
                $amount = round($newTotal * ((float) $allocation->amount / $oldTotal), 2);
                $remaining -= $amount;
            } else {
                $amount = round($newTotal / $allocations->count(), 2);
                $remaining -= $amount;
            }

            $allocation->update(['amount' => $amount]);
        }
    }

    private function maxAllocatableAmount(SupplierPayment $payment): float
    {
        return round((float) $payment->allocations
            ->map(function ($allocation) use ($payment) {
                if (! $allocation->purchase) {
                    return 0;
                }

                $otherPaid = (float) $allocation->purchase->paymentAllocations()
                    ->where('supplier_payment_id', '!=', $payment->id)
                    ->sum('amount');

                return max(0, (float) $allocation->purchase->total_price - $otherPaid);
            })
            ->sum(), 2);
    }
}
