<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WaterFilter;
use App\Support\SalePriceCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSaleAction
{
    public function execute(Sale $sale, array $data): Sale
    {
        $customer = Customer::findOrFail($data['customer_id']);
        $items = $data['items'] ?? [];

        $calculator = SalePriceCalculator::make()
            ->withItems($items)
            ->withDiscount((float) ($data['discount'] ?? 0))
            ->withVat((bool) ($data['with_vat'] ?? false));

        $isInstallment = ($data['payment_type'] ?? 'cash') === 'installment';

        if ($isInstallment) {
            $calculator->withInstallment(
                (float) ($data['down_payment'] ?? 0),
                (int) ($data['installment_months'] ?? 0),
                (float) ($data['interest_rate'] ?? 0)
            );
        }

        $prices = $calculator->toArray();

        return DB::transaction(function () use ($sale, $data, $customer, $isInstallment, $items, $prices) {
            $createdAt = $this->resolveCreatedAt($sale, data_get($data, 'created_at'));

            // find filter installed date for this customer if any
            $filterInstalledAt = WaterFilter::where('customer_id', $customer->id)->first()?->installed_at;
            // Restore old stock quantities
            foreach ($sale->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->increment('quantity', $oldItem->quantity);
                }
            }

            // Delete old movements and items
            ProductMovement::where('movable_type', Sale::class)
                ->where('movable_id', $sale->id)
                ->delete();

            $sale->items()->delete();

            // compute installment start date according to flag
            $installmentStart = null;
            if ($isInstallment) {
                if (! empty($data['useFilterInstalledDate'])) {
                    if (! empty($filterInstalledAt)) {
                        $installmentStart = $filterInstalledAt instanceof \Illuminate\Support\Carbon
                            ? $filterInstalledAt->format('Y-m-d')
                            : (string) $filterInstalledAt;
                    } else {
                        $installmentStart = $createdAt->format('Y-m-d');
                    }
                } else {
                    $installmentStart = ! empty($data['installment_start_date']) ? $data['installment_start_date'] : null;
                }
            }

            // Update sale record
            $sale->update([
                'dealer_name' => $data['dealer_name'] ?? null,
                'total_price' => $prices['grand_total'],
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'discount_value' => $prices['discount_amount'],
                'interest_rate' => $isInstallment ? (float) ($data['interest_rate'] ?? 0) : null,
                'installment_amount' => $isInstallment ? $prices['installment_amount'] : null,
                'installment_months' => $isInstallment ? (int) $data['installment_months'] : null,
                'installment_start_date' => $installmentStart,
                'with_vat' => (bool) ($data['with_vat'] ?? false),
                'customer_id' => $customer->id,
                'created_at' => $createdAt,
            ]);

            // Create new sale items
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ((int) $product->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => __('keywords.not_available') . ': ' . $product->name,
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

            // If requested, propagate installed_at to previous installment sales for this customer
            if ($isInstallment && ! empty($data['useFilterInstalledDate']) && ! empty($filterInstalledAt)) {
                Sale::where('customer_id', $customer->id)
                    ->where('payment_type', 'installment')
                    ->update(['installment_start_date' => $filterInstalledAt instanceof \Illuminate\Support\Carbon ? $filterInstalledAt->format('Y-m-d') : (string) $filterInstalledAt]);
            }

            return $sale->fresh(['items', 'customer']);
        });
    }

    private function resolveCreatedAt(Sale $sale, mixed $createdAt): CarbonInterface
    {
        if (auth()->user()?->can('manage_created_at') && filled($createdAt)) {
            return Carbon::parse((string) $createdAt);
        }

        return $sale->created_at;
    }
}
