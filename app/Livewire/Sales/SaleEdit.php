<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleEdit extends Component
{
    public Sale $sale;

    public ?int $customer_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public string $dealer_name = '';

    /** @var array<int, array{product_id: string, product_name: string, sell_price: string, cost_price: string, quantity: string}> */
    public array $items = [];

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load(['items.product', 'paymentAllocations.customerPayment']);

        $this->customer_id = $sale->customer_id;
        $this->payment_type = $sale->isInstallment() ? 'installment' : 'cash';
        $this->down_payment = (string) $sale->down_payment;
        $this->installment_months = (string) ($sale->installment_months ?? '');
        $this->dealer_name = $sale->dealer_name ?? '';

        $this->items = $sale->items->map(function ($item) {
            return [
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product?->name ?? __('keywords.not_specified'),
                'sell_price' => (string) $item->sell_price,
                'cost_price' => (string) $item->cost_price,
                'quantity' => (string) $item->quantity,
            ];
        })->toArray();

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function updatedPaymentType(string $value): void
    {
        if ($value !== 'installment') {
            $this->down_payment = '0';
            $this->installment_months = '';
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'sell_price' => '',
            'cost_price' => '',
            'quantity' => '1',
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'product_id') {
            $index = (int) $parts[0];
            $productId = $value;

            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->items[$index]['product_name'] = $product->name;
                    $this->items[$index]['sell_price'] = (string) $product->cost_price;
                    $this->items[$index]['cost_price'] = (string) $product->cost_price;
                }
            }
        }
    }

    public function getTotalPriceProperty(): float
    {
        return collect($this->items)->sum(function ($item) {
            return ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0));
        });
    }

    public function getRemainingAfterDownPaymentProperty(): float
    {
        return max(0, $this->total_price - (float) ($this->down_payment ?: 0));
    }

    public function getInstallmentAmountProperty(): float
    {
        $months = (int) ($this->installment_months ?: 0);
        if ($months <= 0) {
            return 0;
        }

        return round($this->remaining_after_down_payment / $months, 2);
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'dealer_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sell_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'dealer_name' => __('keywords.dealer_name'),
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $attrs["items.{$i}.product_id"] = __('keywords.product')." #{$n}";
            $attrs["items.{$i}.sell_price"] = __('keywords.sell_price')." #{$n}";
            $attrs["items.{$i}.quantity"] = __('keywords.quantity')." #{$n}";
        }

        return $attrs;
    }

    public function update(): void
    {
        $this->validate();

        $customer = Customer::findOrFail($this->customer_id);
        $totalPrice = $this->total_price;
        $isInstallment = $this->payment_type === 'installment';
        $months = $isInstallment ? (int) $this->installment_months : null;
        $installmentAmount = $isInstallment ? $this->installment_amount : null;

        DB::transaction(function () use ($customer, $totalPrice, $isInstallment, $months, $installmentAmount) {
            foreach ($this->sale->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->increment('quantity', $oldItem->quantity);
                }
            }

            ProductMovement::where('movable_type', Sale::class)
                ->where('movable_id', $this->sale->id)
                ->delete();

            $this->sale->items()->delete();

            $this->sale->update([
                'dealer_name' => $this->dealer_name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'customer_id' => $customer->id,
            ]);

            foreach ($this->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ((int) $product->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => __('keywords.not_available').': '.$product->name,
                    ]);
                }

                SaleItem::create([
                    'sell_price' => (float) $item['sell_price'],
                    'cost_price' => (float) ($item['cost_price'] ?: $item['sell_price']),
                    'quantity' => $quantity,
                    'sale_id' => $this->sale->id,
                    'product_id' => $product->id,
                ]);

                $product->decrement('quantity', $quantity);

                ProductMovement::create([
                    'quantity' => -$quantity,
                    'movable_type' => Sale::class,
                    'movable_id' => $this->sale->id,
                    'product_id' => $product->id,
                ]);
            }
        });

        session()->flash('success', __('keywords.sale_updated'));
        $this->redirect(route('sales'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.sale-edit', [
            'customers' => Customer::orderBy('name')->pluck('name', 'id')->all(),
            'products' => Product::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
