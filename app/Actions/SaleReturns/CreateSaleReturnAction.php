<?php

declare(strict_types=1);

namespace App\Actions\SaleReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class CreateSaleReturnAction
{
    public function execute(int $saleId, array $data): SaleReturn
    {
        $sale = Sale::findOrFail($saleId);

        return DB::transaction(function () use ($sale, $data) {
            $totalPrice = $this->calculateTotalPrice($data['items']);

            $saleReturn = SaleReturn::create([
                'total_price' => $totalPrice,
                'reason' => $data['reason'] ?? null,
                'cash_refund' => (bool) ($data['cash_refund'] ?? false),
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'created_at' => $this->resolveCreatedAt(data_get($data, 'created_at')),
            ]);

            $this->createReturnItems($saleReturn, $data['items']);

            return $saleReturn;
        });
    }

    private function calculateTotalPrice(array $items): float
    {
        return collect($items)
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ((float) ($item['sell_price'] ?? 0)) * ((int) ($item['return_quantity'] ?? 0)));
    }

    private function createReturnItems(SaleReturn $saleReturn, array $items): void
    {
        foreach ($items as $item) {
            if (!($item['selected'] ?? false) || ((int) ($item['return_quantity'] ?? 0)) <= 0) {
                continue;
            }

            $quantity = (int) $item['return_quantity'];

            SaleReturnItem::create([
                'sell_price' => (float) $item['sell_price'],
                'quantity' => $quantity,
                'sale_return_id' => $saleReturn->id,
                'product_id' => $item['product_id'],
            ]);

            $product = Product::findOrFail($item['product_id']);
            $product->increment('quantity', $quantity);

            ProductMovement::create([
                'quantity' => $quantity,
                'movable_type' => SaleReturn::class,
                'movable_id' => $saleReturn->id,
                'product_id' => $item['product_id'],
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
}
