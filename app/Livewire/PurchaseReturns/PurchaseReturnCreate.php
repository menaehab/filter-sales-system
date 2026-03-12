<?php

namespace App\Livewire\PurchaseReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseReturnCreate extends Component
{
    public string $purchase_number = '';
    public ?Purchase $purchase = null;
    public string $reason = '';
    public bool $cash_refund = false;

    /** @var array<int, array{product_id: int, product_name: string, cost_price: string, available_quantity: float, return_quantity: string, selected: bool}> */
    public array $items = [];

    public function updatedPurchaseNumber(): void
    {
        $this->resetPurchase();

        if (blank($this->purchase_number)) {
            return;
        }

        $purchase = Purchase::with('items.product')->where('number', $this->purchase_number)->first();

        if (! $purchase) {
            $this->addError('purchase_number', __('keywords.purchase_not_found'));
            return;
        }

        $this->purchase = $purchase;
        $this->items = $purchase->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'cost_price' => (string) $item->cost_price,
            'available_quantity' => (float) $item->quantity,
            'return_quantity' => '',
            'selected' => false,
        ])->toArray();
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
            'purchase_number' => 'required',
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
            'purchase_number' => __('keywords.purchase_number'),
            'reason' => __('keywords.reason'),
            'cash_refund' => __('keywords.cash_refund'),
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $attrs["items.{$i}.return_quantity"] = __('keywords.quantity') . " #{$n}";
        }

        return $attrs;
    }

    public function save(): void
    {
        if ($this->selected_items_count === 0) {
            $this->addError('items', __('keywords.select_at_least_one_item'));
            return;
        }

        $this->validate();

        DB::transaction(function () {
            $totalPrice = $this->total_return_price;

            $purchaseReturn = PurchaseReturn::create([
                'total_price' => $totalPrice,
                'reason' => $this->reason ?: null,
                'cash_refund' => $this->cash_refund,
                'purchase_id' => $this->purchase->id,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                if (! $item['selected'] || (float) ($item['return_quantity'] ?: 0) <= 0) {
                    continue;
                }

                $quantity = (int) $item['return_quantity'];

                PurchaseReturnItem::create([
                    'cost_price' => (float) $item['cost_price'],
                    'quantity' => $quantity,
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                ]);

                // Decrease product stock
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('quantity', $quantity);

                // Record product movement (negative)
                ProductMovement::create([
                    'quantity' => -$quantity,
                    'movable_type' => PurchaseReturn::class,
                    'movable_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                ]);
            }
        });

        session()->flash('success', __('keywords.purchase_return_created'));
        $this->redirect(route('purchase-returns'), navigate: true);
    }

    private function resetPurchase(): void
    {
        $this->purchase = null;
        $this->items = [];
        $this->resetErrorBag('purchase_number');
    }

    public function render()
    {
        return view('livewire.purchase-returns.purchase-return-create');
    }
}
