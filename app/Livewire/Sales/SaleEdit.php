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
    private const VAT_RATE = 14;

    private const INSTALLMENT_MONTHLY_SURCHARGE = 100;

    public Sale $sale;

    public ?int $customer_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public string $interest_rate = '0';

    public string $discount = '0';

    public bool $with_vat = false;

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
        $this->interest_rate = (string) ($sale->interest_rate ?? '0');
        $this->discount = (string) ($sale->discount_value ?? '0');
        $this->with_vat = (bool) ($sale->with_vat ?? false);
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
            $this->interest_rate = '0';
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

    public function getBaseTotalProperty(): float
    {
        return collect($this->items)->sum(function ($item) {
            return ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0));
        });
    }

    public function getDiscountAmountProperty(): float
    {
        $discount = max(0, (float) ($this->discount ?: 0));

        return min($this->base_total, $discount);
    }

    public function getTotalAfterDiscountProperty(): float
    {
        return max(0, $this->base_total - $this->discount_amount);
    }

    public function getVatAmountProperty(): float
    {
        if (! $this->with_vat) {
            return 0;
        }

        return round($this->total_after_discount * (self::VAT_RATE / 100), 2);
    }

    public function getSubtotalAfterVatProperty(): float
    {
        return $this->total_after_discount + $this->vat_amount;
    }

    public function getRemainingAfterDownPaymentProperty(): float
    {
        return max(0, $this->subtotal_after_vat - (float) ($this->down_payment ?: 0));
    }

    public function getInterestAmountProperty(): float
    {
        if ($this->payment_type !== 'installment') {
            return 0;
        }

        $rate = max(0, (float) ($this->interest_rate ?: 0));

        return round($this->remaining_after_down_payment * ($rate / 100), 2);
    }

    public function getInstallmentMonthsSurchargeTotalProperty(): float
    {
        if ($this->payment_type !== 'installment') {
            return 0;
        }

        $months = (int) ($this->installment_months ?: 0);
        if ($months < 3) {
            return 0;
        }

        return $months * self::INSTALLMENT_MONTHLY_SURCHARGE;
    }

    public function getInstallmentTotalProperty(): float
    {
        if ($this->payment_type !== 'installment') {
            return 0;
        }

        return $this->remaining_after_down_payment + $this->interest_amount + $this->installment_months_surcharge_total;
    }

    public function getInstallmentAmountProperty(): float
    {
        $months = (int) ($this->installment_months ?: 0);
        if ($months <= 0) {
            return 0;
        }

        return round($this->installment_total / $months, 2);
    }

    public function getTotalPriceProperty(): float
    {
        if ($this->payment_type !== 'installment') {
            return $this->subtotal_after_vat;
        }

        return $this->subtotal_after_vat + $this->interest_amount + $this->installment_months_surcharge_total;
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'interest_rate' => 'required_if:payment_type,installment|nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'with_vat' => 'boolean',
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
            'interest_rate' => __('keywords.interest_rate'),
            'discount' => __('keywords.discount'),
            'with_vat' => __('keywords.apply_vat'),
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
        $baseTotal = $this->base_total;
        $discountAmount = min($baseTotal, max(0, (float) ($this->discount ?: 0)));
        $totalAfterDiscount = max(0, $baseTotal - $discountAmount);
        $vatAmount = $this->with_vat ? round($totalAfterDiscount * (self::VAT_RATE / 100), 2) : 0;
        $subtotalAfterVat = $totalAfterDiscount + $vatAmount;
        $totalPrice = $this->total_price;
        $isInstallment = $this->payment_type === 'installment';
        $months = $isInstallment ? (int) $this->installment_months : null;
        $installmentAmount = $isInstallment ? $this->installment_amount : null;
        $interestRate = $isInstallment ? max(0, (float) ($this->interest_rate ?: 0)) : null;

        DB::transaction(function () use ($customer, $totalPrice, $isInstallment, $months, $installmentAmount, $interestRate, $discountAmount) {
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
                'discount_value' => $discountAmount,
                'interest_rate' => $interestRate,
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'with_vat' => $this->with_vat,
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
