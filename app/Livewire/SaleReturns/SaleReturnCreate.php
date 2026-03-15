<?php

namespace App\Livewire\SaleReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleReturnCreate extends Component
{
    public string $sale_number = '';
    public ?Sale $sale = null;
    public string $reason = '';
    public bool $cash_refund = false;

    /** @var array<int, array{product_id: int, product_name: string, sell_price: string, available_quantity: float, return_quantity: string, selected: bool}> */
    public array $items = [];

    public function updatedSaleNumber(): void
    {
        $this->resetSale();

        if (blank($this->sale_number)) {
            return;
        }

        $sale = Sale::with(['items.product', 'customer'])->where('number', $this->sale_number)->first();

        if (! $sale) {
            $this->addError('sale_number', __('keywords.sale_not_found'));
            return;
        }

        $this->sale = $sale;
        $this->items = $sale->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? __('keywords.not_specified'),
            'sell_price' => (string) $item->sell_price,
            'available_quantity' => (float) $item->quantity,
            'return_quantity' => '',
            'selected' => false,
        ])->toArray();
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
            'sale_number' => 'required',
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
            'sale_number' => __('keywords.sale_number'),
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

            $saleReturn = SaleReturn::create([
                'total_price' => $totalPrice,
                'reason' => $this->reason ?: null,
                'cash_refund' => $this->cash_refund,
                'sale_id' => $this->sale->id,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                if (! $item['selected'] || (float) ($item['return_quantity'] ?: 0) <= 0) {
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
        });

        session()->flash('success', __('keywords.sale_return_created'));
        $this->redirect(route('sale-returns'), navigate: true);
    }

    private function resetSale(): void
    {
        $this->sale = null;
        $this->items = [];
        $this->resetErrorBag('sale_number');
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-create');
    }
}
