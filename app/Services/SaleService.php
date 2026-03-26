<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CreateSaleAction;
use App\Actions\DeleteSaleAction;
use App\Actions\UpdateSaleAction;
use App\Models\Customer;
use App\Models\Sale;
use App\Support\SalePriceCalculator;

final class SaleService
{
    public function __construct(
        private readonly CreateSaleAction $createAction,
        private readonly UpdateSaleAction $updateAction,
        private readonly DeleteSaleAction $deleteAction,
    ) {}

    public function create(array $data): Sale
    {
        return $this->createAction->execute($data);
    }

    public function update(Sale $sale, array $data): Sale
    {
        return $this->updateAction->execute($sale, $data);
    }

    public function delete(Sale $sale): void
    {
        $this->deleteAction->execute($sale);
    }

    public function calculatePrices(array $cart, array $options = []): array
    {
        $customerId = $options['customer_id'] ?? null;
        $customer = $customerId ? Customer::find($customerId) : null;
        $appliedCredit = $customer ? $customer->available_credit : 0;

        $calculator = SalePriceCalculator::make()
            ->withItems($cart)
            ->withDiscount((float) ($options['discount'] ?? 0))
            ->withVat((bool) ($options['with_vat'] ?? false))
            ->withAppliedCredit(min($appliedCredit, $this->calculateBaseSubtotal($cart, $options)));

        if (($options['payment_type'] ?? 'cash') === 'installment') {
            $calculator->withInstallment(
                (float) ($options['down_payment'] ?? 0),
                (int) ($options['installment_months'] ?? 0),
                (float) ($options['interest_rate'] ?? 0)
            );
        }

        return array_merge($calculator->toArray(), [
            'available_credit' => $appliedCredit,
        ]);
    }

    private function calculateBaseSubtotal(array $cart, array $options): float
    {
        $calculator = SalePriceCalculator::make()
            ->withItems($cart)
            ->withDiscount((float) ($options['discount'] ?? 0))
            ->withVat((bool) ($options['with_vat'] ?? false));

        return $calculator->subtotalAfterVat();
    }

    public function getCustomerInstallmentQueue(int $customerId)
    {
        return Sale::with('paymentAllocations')
            ->where('customer_id', $customerId)
            ->where('installment_months', '>', 0)
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Sale $item) => $item->remaining_amount > 0)
            ->values();
    }
}
