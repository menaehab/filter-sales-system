<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use App\Support\SalePriceCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateSaleAction
{
    public function execute(array $data): Sale
    {
        $customer = Customer::findOrFail($data['customer_id']);

        $calculator = SalePriceCalculator::make()
            ->withItems($data['cart'])
            ->withDiscount((float) ($data['discount'] ?? 0))
            ->withVat((bool) ($data['with_vat'] ?? false))
            ->withAppliedCredit(min($customer->available_credit, $this->calculateSubtotalWithVat($data)));

        $isInstallment = ($data['payment_type'] ?? 'cash') === 'installment';

        if ($isInstallment) {
            $calculator->withInstallment(
                (float) ($data['down_payment'] ?? 0),
                (int) ($data['installment_months'] ?? 0),
                (float) ($data['interest_rate'] ?? 0)
            );
        }

        $appliedCredit = min($customer->available_credit, $calculator->subtotalAfterVat());
        $calculator->withAppliedCredit($appliedCredit);

        $prices = $calculator->toArray();
        $downPayment = $prices['down_payment'];
        $installmentAmount = $isInstallment && (int) ($data['installment_months'] ?? 0) > 0
            ? $prices['installment_amount']
            : null;

        return DB::transaction(function () use (
            $data,
            $customer,
            $isInstallment,
            $downPayment,
            $appliedCredit,
            $installmentAmount,
            $prices
        ) {
            $createdAt = $this->resolveCreatedAt(data_get($data, 'created_at'));

            $sale = Sale::create([
                'dealer_name' => $data['dealer_name'] ?? null,
                'user_name' => auth()->user()->name,
                'total_price' => $prices['grand_total'],
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'discount_value' => $prices['discount_amount'],
                'interest_rate' => $isInstallment ? (float) ($data['interest_rate'] ?? 0) : null,
                'installment_amount' => $installmentAmount,
                'installment_months' => $isInstallment ? (int) ($data['installment_months'] ?? 0) : null,
                'with_vat' => (bool) ($data['with_vat'] ?? false),
                'user_id' => auth()->id(),
                'customer_id' => $customer->id,
                'created_at' => $createdAt,
            ]);

            $this->createSaleItems($sale, $data['cart']);
            $this->createPayments($sale, $customer, $downPayment, $appliedCredit, $isInstallment, $createdAt);

            if (! empty($data['includeWaterReading']) && $data['includeWaterReading']) {
                $this->createWaterReading($data, $customer);
            }

            return $sale;
        });
    }

    private function calculateSubtotalWithVat(array $data): float
    {
        $baseTotal = collect($data['cart'])->sum(function ($item) {
            return ((float) ($item['sell_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });

        $discount = min($baseTotal, (float) ($data['discount'] ?? 0));
        $totalAfterDiscount = max(0, $baseTotal - $discount);

        $vatAmount = ($data['with_vat'] ?? false)
            ? round($totalAfterDiscount * 0.14, 2)
            : 0;

        return $totalAfterDiscount + $vatAmount;
    }

    private function createSaleItems(Sale $sale, array $cart): void
    {
        foreach ($cart as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];

            if ((int) $product->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'cart' => __('keywords.low_stock_warning').': '.$product->name.' ('.__('keywords.available').': '.$product->quantity.')',
                ]);
            }

            SaleItem::create([
                'sell_price' => (float) $item['sell_price'],
                'cost_price' => (float) ($item['cost_price'] ?? $item['sell_price']),
                'quantity' => $quantity,
                'sale_id' => $sale->id,
                'product_id' => $product->id,
            ]);

            $product->decrement('quantity', $quantity);

            ProductMovement::create([
                'quantity' => -$quantity,
                'movable_type' => Sale::class,
                'movable_id' => $sale->id,
                'product_id' => $product->id,
            ]);
        }
    }

    private function createPayments(
        Sale $sale,
        Customer $customer,
        float $downPayment,
        float $appliedCredit,
        bool $isInstallment,
        Carbon $createdAt
    ): void {
        if ($downPayment > 0) {
            $payment = CustomerPayment::create([
                'amount' => $downPayment,
                'payment_method' => 'cash',
                'note' => $isInstallment ? __('keywords.down_payment') : __('keywords.cash_payment'),
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'created_at' => $createdAt,
            ]);

            CustomerPaymentAllocation::create([
                'amount' => $downPayment,
                'customer_payment_id' => $payment->id,
                'sale_id' => $sale->id,
            ]);
        }

        if ($appliedCredit > 0) {
            $creditPayment = CustomerPayment::create([
                'amount' => $appliedCredit,
                'payment_method' => 'customer_credit',
                'note' => __('keywords.applied_customer_credit'),
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'created_at' => $createdAt,
            ]);

            CustomerPaymentAllocation::create([
                'amount' => $appliedCredit,
                'customer_payment_id' => $creditPayment->id,
                'sale_id' => $sale->id,
            ]);
        }
    }

    private function resolveCreatedAt(mixed $createdAt): Carbon
    {
        if (auth()->user()?->can('manage_created_at') && filled($createdAt)) {
            return Carbon::parse((string) $createdAt);
        }

        return Carbon::now();
    }

    private function createWaterReading(array $data, Customer $customer): void
    {
        $filterId = $data['water_filter_id'] ?? null;

        if (! empty($data['createNewFilter']) && ! empty($data['newFilter'])) {
            $newFilter = WaterFilter::create([
                'filter_model' => $data['newFilter']['filter_model'],
                'address' => $data['newFilter']['address'],
                'customer_id' => $customer->id,
            ]);
            $filterId = $newFilter->id;
        }

        if ($filterId && ! empty($data['waterReading'])) {
            WaterReading::create([
                'technician_name' => $data['waterReading']['technician_name'],
                'tds' => $data['waterReading']['tds'],
                'water_quality' => $data['waterReading']['water_quality'],
                'before_installment' => $data['waterReading']['before_installment'] ?? false,
                'water_filter_id' => $filterId,
            ]);

            if (
                ! empty($data['waterReading']['before_installment'])
                && ! empty($data['includeAfterInstallationReading'])
                && ! empty($data['afterWaterReading'])
            ) {
                WaterReading::create([
                    'technician_name' => $data['afterWaterReading']['technician_name'],
                    'tds' => $data['afterWaterReading']['tds'],
                    'water_quality' => $data['afterWaterReading']['water_quality'],
                    'before_installment' => false,
                    'water_filter_id' => $filterId,
                ]);
            }
        }
    }
}
