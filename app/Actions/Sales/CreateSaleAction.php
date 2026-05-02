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

            // Determine filter installed date if available
            $filterInstalledAt = null;
            if (! empty($data['createNewFilter']) && ! empty($data['newFilter'])) {
                $isInstalled = (bool) data_get($data, 'newFilter.is_installed', false);
                if ($isInstalled) {
                    $filterInstalledAt = data_get($data, 'newFilter.installed_at');
                }
            } elseif (! empty($data['water_filter_id'])) {
                $filter = WaterFilter::find($data['water_filter_id']);
                $filterInstalledAt = $filter?->installed_at;
            } else {
                $existingFilter = WaterFilter::where('customer_id', $customer->id)->first();
                $filterInstalledAt = $existingFilter?->installed_at;
            }

            // Compute installment start date according to flag and availability
            $installmentStart = null;
            if ($isInstallment) {
                if (! empty($data['useFilterInstalledDate'])) {
                    if (! empty($filterInstalledAt)) {
                        $installmentStart = $filterInstalledAt instanceof \Illuminate\Support\Carbon
                            ? $filterInstalledAt->format('Y-m-d')
                            : (string) $filterInstalledAt;
                    } else {
                        // no filter installed -> fall back to created_at
                        $installmentStart = $createdAt->format('Y-m-d');
                    }
                } else {
                    $installmentStart = ! empty($data['installment_start_date'])
                        ? $data['installment_start_date']
                        : $createdAt->format('Y-m-d');
                }
            }

            $sale = Sale::create([
                'dealer_name' => $data['dealer_name'] ?? null,
                'user_name' => auth()->user()->name,
                'total_price' => $prices['grand_total'],
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'discount_value' => $prices['discount_amount'],
                'interest_rate' => $isInstallment ? (float) ($data['interest_rate'] ?? 0) : null,
                'installment_amount' => $installmentAmount,
                'installment_months' => $isInstallment ? (int) ($data['installment_months'] ?? 0) : null,
                'installment_start_date' => $installmentStart,
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

            // If requested, propagate installed_at to previous installment sales for this customer
            if ($isInstallment && ! empty($data['useFilterInstalledDate']) && ! empty($filterInstalledAt)) {
                Sale::where('customer_id', $customer->id)
                    ->where('payment_type', 'installment')
                    ->update(['installment_start_date' => $filterInstalledAt instanceof \Illuminate\Support\Carbon ? $filterInstalledAt->format('Y-m-d') : (string) $filterInstalledAt]);
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
            if (WaterFilter::where('customer_id', $customer->id)->exists()) {
                throw ValidationException::withMessages([
                    'customer_id' => __('validation.unique', ['attribute' => __('keywords.customer')]),
                ]);
            }

            $isInstalled = (bool) data_get($data, 'newFilter.is_installed', false);

            $newFilter = WaterFilter::create([
                'filter_model' => $data['newFilter']['filter_model'],
                'address' => $data['newFilter']['address'],
                'is_installed' => $isInstalled,
                'installed_at' => $isInstalled ? data_get($data, 'newFilter.installed_at') : null,
                'customer_id' => $customer->id,
            ]);
            $filterId = $newFilter->id;
        }

        $waterReading = (array) data_get($data, 'waterReading', []);

        if (! $filterId || ! $this->hasCompleteReadingPayload($waterReading)) {
            return;
        }

        WaterReading::create([
            'technician_name' => $waterReading['technician_name'],
            'tds' => $waterReading['tds'],
            'water_quality' => $waterReading['water_quality'],
            'before_installment' => (bool) ($waterReading['before_installment'] ?? false),
            'water_filter_id' => $filterId,
        ]);

        $afterReading = (array) data_get($data, 'afterWaterReading', []);

        if (
            (bool) ($waterReading['before_installment'] ?? false)
            && ! empty($data['includeAfterInstallationReading'])
            && $this->hasCompleteReadingPayload($afterReading)
        ) {
            WaterReading::create([
                'technician_name' => $afterReading['technician_name'],
                'tds' => $afterReading['tds'],
                'water_quality' => $afterReading['water_quality'],
                'before_installment' => false,
                'water_filter_id' => $filterId,
            ]);
        }
    }

    private function hasCompleteReadingPayload(array $reading): bool
    {
        return filled(data_get($reading, 'technician_name'))
            && filled(data_get($reading, 'tds'))
            && filled(data_get($reading, 'water_quality'));
    }
}
