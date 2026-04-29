<?php

namespace App\Livewire\Sales;

use App\Actions\Sales\UpdateSaleAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Support\SalePriceCalculator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleEdit extends Component
{
    // IMPORTANT: Store only the ID, not the model (Livewire v4 best practice)
    #[Locked]
    public int $saleId;

    public ?int $customer_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public string $interest_rate = '0';

    public string $installment_start_date = '';

    public string $discount = '0';

    public bool $with_vat = false;

    public string $dealer_name = '';

    public string $created_at = '';

    /** @var array<int, array{product_id: string, product_name: string, sell_price: string, cost_price: string, quantity: string}> */
    public array $items = [];

    public function mount(Sale $sale): void
    {
        // Store only the ID
        $this->saleId = $sale->id;

        // Load and map data to primitives
        $sale->load(['items.product', 'paymentAllocations.customerPayment']);

        $this->customer_id = $sale->customer_id;
        $this->payment_type = $sale->isInstallment() ? 'installment' : 'cash';
        $this->down_payment = (string) $sale->down_payment;
        $this->installment_months = (string) ($sale->installment_months ?? '');
        $this->interest_rate = (string) ($sale->interest_rate ?? '0');
        $this->installment_start_date = $sale->installment_start_date?->format('Y-m-d') ?? '';
        $this->discount = (string) ($sale->discount_value ?? '0');
        $this->with_vat = (bool) ($sale->with_vat ?? false);
        $this->dealer_name = $sale->dealer_name ?? '';
        $this->created_at = $sale->created_at?->format('Y/m/d H:i') ?? now()->format('Y/m/d H:i');

        $this->items = $sale->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'product_name' => $item->product?->name ?? __('keywords.not_specified'),
            'sell_price' => (string) $item->sell_price,
            'cost_price' => (string) $item->cost_price,
            'quantity' => (string) $item->quantity,
        ])->toArray();

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    // ==========================================
    // COMPUTED PROPERTY - Rebuild model when needed
    // ==========================================

    #[Computed]
    public function sale(): Sale
    {
        return Sale::with(['items.product', 'paymentAllocations.customerPayment'])
            ->findOrFail($this->saleId);
    }

    // ==========================================
    // FORM HANDLERS
    // ==========================================

    public function updatedPaymentType(string $value): void
    {
        if ($value !== 'installment') {
            $this->down_payment = '0';
            $this->installment_months = '';
            $this->interest_rate = '0';
            $this->installment_start_date = '';
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

    // ==========================================
    // COMPUTED PRICE CALCULATIONS
    // ==========================================

    #[Computed]
    public function calculator(): SalePriceCalculator
    {
        $calculator = SalePriceCalculator::make()
            ->withItems($this->items)
            ->withDiscount((float) ($this->discount ?? 0))
            ->withVat($this->with_vat);

        if ($this->payment_type === 'installment') {
            $calculator->withInstallment(
                (float) ($this->down_payment ?? 0),
                (int) ($this->installment_months ?? 0),
                (float) ($this->interest_rate ?? 0)
            );
        }

        return $calculator;
    }

    #[Computed]
    public function baseTotal(): float
    {
        return $this->calculator->baseTotal();
    }

    #[Computed]
    public function discountAmount(): float
    {
        return $this->calculator->discountAmount();
    }

    #[Computed]
    public function totalAfterDiscount(): float
    {
        return $this->calculator->totalAfterDiscount();
    }

    #[Computed]
    public function vatAmount(): float
    {
        return $this->calculator->vatAmount();
    }

    #[Computed]
    public function subtotalAfterVat(): float
    {
        return $this->calculator->subtotalAfterVat();
    }

    #[Computed]
    public function remainingAfterDownPayment(): float
    {
        return $this->calculator->remainingAfterDownPayment();
    }

    #[Computed]
    public function interestAmount(): float
    {
        return $this->calculator->interestAmount();
    }

    #[Computed]
    public function installmentMonthsSurchargeTotal(): float
    {
        return $this->calculator->installmentSurchargeTotal();
    }

    #[Computed]
    public function installmentTotal(): float
    {
        return $this->calculator->installmentTotal();
    }

    #[Computed]
    public function installmentAmount(): float
    {
        return $this->calculator->installmentAmount();
    }

    #[Computed]
    public function totalPrice(): float
    {
        return $this->calculator->grandTotal();
    }

    // ==========================================
    // VALIDATION
    // ==========================================

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'interest_rate' => 'required_if:payment_type,installment|nullable|numeric|min:0|max:100',
            'installment_start_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'with_vat' => 'boolean',
            'dealer_name' => 'nullable|string|max:255',
            'created_at' => 'nullable|date',
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
            'installment_start_date' => __('keywords.installment_start_date'),
            'discount' => __('keywords.discount'),
            'with_vat' => __('keywords.apply_vat'),
            'dealer_name' => __('keywords.dealer_name'),
            'created_at' => __('keywords.created_at'),
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $attrs["items.{$i}.product_id"] = __('keywords.product')." #{$n}";
            $attrs["items.{$i}.sell_price"] = __('keywords.sell_price')." #{$n}";
            $attrs["items.{$i}.quantity"] = __('keywords.quantity')." #{$n}";
        }

        return $attrs;
    }

    // ==========================================
    // UPDATE ACTION
    // ==========================================

    public function update(UpdateSaleAction $action): void
    {
        $this->validate();

        $action->execute($this->sale, $this->getUpdateData());

        session()->flash('success', __('keywords.sale_updated'));
        $this->redirect(route('sales'), navigate: true);
    }

    private function getUpdateData(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'payment_type' => $this->payment_type,
            'down_payment' => $this->down_payment,
            'installment_months' => $this->installment_months,
            'interest_rate' => $this->interest_rate,
            'installment_start_date' => $this->installment_start_date,
            'discount' => $this->discount,
            'with_vat' => $this->with_vat,
            'dealer_name' => $this->dealer_name,
            'created_at' => $this->created_at,
            'items' => $this->items,
        ];
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    // ==========================================
    // COMPUTED DATA FOR VIEW
    // ==========================================

    #[Computed]
    public function customers(): array
    {
        return Customer::orderBy('name')->pluck('name', 'id')->all();
    }

    #[Computed]
    public function products(): array
    {
        return Product::orderBy('name')->pluck('name', 'id')->all();
    }

    public function render()
    {
        return view('livewire.sales.sale-edit', [
            'customers' => $this->customers,
            'products' => $this->products,
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
