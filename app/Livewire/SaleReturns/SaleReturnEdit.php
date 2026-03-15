<?php

namespace App\Livewire\SaleReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleReturnEdit extends Component
{
    public SaleReturn $saleReturn;
    public string $reason = '';
    public bool $cash_refund = false;

    /** @var array<int, array{product_id: int, product_name: string, sell_price: string, available_quantity: float, return_quantity: string, selected: bool, original_return_quantity: float}> */
    public array $items = [];

    public function mount(SaleReturn $saleReturn): void
    {
        $this->saleReturn = $saleReturn->load(['sale.items.product', 'items']);
        $this->reason = $saleReturn->reason ?? '';
        $this->cash_refund = (bool) $saleReturn->cash_refund;

        $returnItems = $saleReturn->items->keyBy('product_id');

        $this->items = $saleReturn->sale->items->map(function ($saleItem) use ($returnItems) {
            $returnItem = $returnItems->get($saleItem->product_id);

            return [
                'product_id' => $saleItem->product_id,
                'product_name' => $saleItem->product?->name ?? __('keywords.not_specified'),
                'sell_price' => (string) $saleItem->sell_price,
                'available_quantity' => (float) $saleItem->quantity,
                'return_quantity' => $returnItem ? (string) $returnItem->quantity : '',
                'selected' => (bool) $returnItem,
                'original_return_quantity' => $returnItem ? (float) $returnItem->quantity : 0,
            ];
        })->toArray();
    }

    public function getTotalReturnPriceProperty(): float
    {
        return collect($this->items)
            ->filter(fn ($item) => $item['selected'])
            ->sum(fn ($item) => ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['return_quantity'] ?: 0)));
    }

    public function getSelectedItemsCountProperty(): int
    {
        return collect($this->items)->filter(fn ($item) => $item['selected'])->count();
    }

    protected function rules(): array
    {
        $rules = [
            'reason' => 'nullable|string|max:1000',
            'cash_refund' => 'boolean',
        ];

        foreach ($this->items as $i => $item) {
            if ($item['selected']) {
                $rules["items.{$i}.return_quantity"] = "required|integer|min:1|max:{$item['available_quantity']}";
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'reason' => __('keywords.reason'),
            'cash_refund' => __('keywords.cash_refund'),
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $attrs["items.{$i}.return_quantity"] = __('keywords.quantity') . " #{$n}";
        }

        return $attrs;
    }

    public function update(): void
    {
        if ($this->selected_items_count === 0) {
            $this->addError('items', __('keywords.select_at_least_one_item'));
            return;
        }

        $this->validate();

        DB::transaction(function () {
            foreach ($this->saleReturn->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->decrement('quantity', $oldItem->quantity);
                }
            }

            ProductMovement::where('movable_type', SaleReturn::class)
                ->where('movable_id', $this->saleReturn->id)
                ->delete();
            $this->saleReturn->items()->delete();

            $totalPrice = $this->total_return_price;

            $this->saleReturn->update([
                'total_price' => $totalPrice,
                'reason' => $this->reason ?: null,
                'cash_refund' => $this->cash_refund,
            ]);

            foreach ($this->items as $item) {
                if (! $item['selected'] || (float) ($item['return_quantity'] ?: 0) <= 0) {
                    continue;
                }

                $quantity = (int) $item['return_quantity'];

                SaleReturnItem::create([
                    'sell_price' => (float) $item['sell_price'],
                    'quantity' => $quantity,
                    'sale_return_id' => $this->saleReturn->id,
                    'product_id' => $item['product_id'],
                ]);

                $product = Product::findOrFail($item['product_id']);
                $product->increment('quantity', $quantity);

                ProductMovement::create([
                    'quantity' => $quantity,
                    'movable_type' => SaleReturn::class,
                    'movable_id' => $this->saleReturn->id,
                    'product_id' => $item['product_id'],
                ]);
            }
        });

        session()->flash('success', __('keywords.sale_return_updated'));
        $this->redirect(route('sale-returns'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-edit');
    }
}
