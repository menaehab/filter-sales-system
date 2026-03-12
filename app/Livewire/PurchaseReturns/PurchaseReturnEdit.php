<?php

namespace App\Livewire\PurchaseReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseReturnEdit extends Component
{
    public PurchaseReturn $purchaseReturn;
    public string $reason = '';
    public bool $cash_refund = false;

    /** @var array<int, array{product_id: int, product_name: string, cost_price: string, available_quantity: float, return_quantity: string, selected: bool, original_return_quantity: int}> */
    public array $items = [];

    public function mount(PurchaseReturn $purchaseReturn): void
    {
        $this->purchaseReturn = $purchaseReturn->load(['purchase.items.product', 'items']);
        $this->reason = $purchaseReturn->reason ?? '';
        $this->cash_refund = (bool) $purchaseReturn->cash_refund;

        $returnItems = $purchaseReturn->items->keyBy('product_id');

        $this->items = $purchaseReturn->purchase->items->map(function ($purchaseItem) use ($returnItems) {
            $returnItem = $returnItems->get($purchaseItem->product_id);

            return [
                'product_id' => $purchaseItem->product_id,
                'product_name' => $purchaseItem->product_name,
                'cost_price' => (string) $purchaseItem->cost_price,
                'available_quantity' => (float) $purchaseItem->quantity,
                'return_quantity' => $returnItem ? (string) $returnItem->quantity : '',
                'selected' => (bool) $returnItem,
                'original_return_quantity' => $returnItem ? (int) $returnItem->quantity : 0,
            ];
        })->toArray();
    }

    public function getTotalReturnPriceProperty(): float
    {
        return collect($this->items)
            ->filter(fn ($item) => $item['selected'])
            ->sum(fn ($item) => ((float) ($item['cost_price'] ?: 0)) * ((float) ($item['return_quantity'] ?: 0)));
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
                $rules["items.{$i}.return_quantity"] = "required|numeric|min:1|max:{$item['available_quantity']}";
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
            // Reverse old stock changes
            foreach ($this->purchaseReturn->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->increment('quantity', $oldItem->quantity);
                }
            }

            // Delete old items and movements
            ProductMovement::where('movable_type', PurchaseReturn::class)
                ->where('movable_id', $this->purchaseReturn->id)
                ->delete();
            $this->purchaseReturn->items()->delete();

            $totalPrice = $this->total_return_price;

            $this->purchaseReturn->update([
                'total_price' => $totalPrice,
                'reason' => $this->reason ?: null,
                'cash_refund' => $this->cash_refund,
            ]);

            foreach ($this->items as $item) {
                if (! $item['selected'] || (float) ($item['return_quantity'] ?: 0) <= 0) {
                    continue;
                }

                $quantity = (int) $item['return_quantity'];

                PurchaseReturnItem::create([
                    'cost_price' => (float) $item['cost_price'],
                    'quantity' => $quantity,
                    'purchase_return_id' => $this->purchaseReturn->id,
                    'product_id' => $item['product_id'],
                ]);

                // Decrease product stock
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('quantity', $quantity);

                // Record product movement (negative)
                ProductMovement::create([
                    'quantity' => -$quantity,
                    'movable_type' => PurchaseReturn::class,
                    'movable_id' => $this->purchaseReturn->id,
                    'product_id' => $item['product_id'],
                ]);
            }
        });

        session()->flash('success', __('keywords.purchase_return_updated'));
        $this->redirect(route('purchase-returns'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase-returns.purchase-return-edit');
    }
}
