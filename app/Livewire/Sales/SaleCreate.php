<?php

namespace App\Livewire\Sales;

use App\Actions\Sales\CreateSaleAction;
use App\Enums\WaterQualityTypeEnum;
use App\Livewire\Traits\HasSaleForm;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Place;
use App\Models\Product;
use App\Models\WaterFilter;
use App\Support\SalePriceCalculator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'pos'])]
class SaleCreate extends Component
{
    use HasSaleForm;

    public string $customerSearch = '';

    public string $search = '';

    public string $activeCategory = 'all';

    public bool $printAfterSave = false;

    public array $newCustomer = [
        'name' => '',
        'place_id' => '',
        'phone' => '',
        'national_number' => '',
        'address' => '',
    ];

    public function updatedPaymentType(string $value): void
    {
        if ($value !== 'installment') {
            $this->down_payment = '0';
            $this->installment_months = '';
            $this->interest_rate = '0';
        }
    }

    public function updatedIncludeWaterReading(bool $value): void
    {
        if (! $value) {
            $this->water_filter_id = null;
            $this->filterSearch = '';
            $this->createNewFilter = false;
            $this->newFilter = [
                'filter_model' => '',
                'address' => '',
            ];
            $this->waterReading = [
                'technician_name' => '',
                'tds' => '',
                'water_quality' => '',
                'before_installment' => false,
            ];
        }
    }

    public function updatedCustomerId(): void
    {
        $this->water_filter_id = null;
        $this->filterSearch = '';
    }

    public function updatedCreateNewFilter(bool $value): void
    {
        if ($value) {
            $this->water_filter_id = null;
            $this->filterSearch = '';
        } else {
            $this->newFilter = [
                'filter_model' => '',
                'address' => '',
            ];
        }
    }

    public function selectFilter(int $filterId, string $filterLabel): void
    {
        $this->water_filter_id = $filterId;
        $this->filterSearch = $filterLabel;
        $this->createNewFilter = false;
    }

    public function setActiveCategory(string $categoryId): void
    {
        $this->activeCategory = $categoryId;
    }

    public function addToCart(int $productId): void
    {
        $product = Product::with('category')->findOrFail($productId);
        $available = (int) $product->quantity;

        if ($available <= 0) {
            session()->flash('warning', __('keywords.out_of_stock_warning').': '.$product->name);

            return;
        }

        $existingIndex = collect($this->cart)->search(fn ($item) => (int) $item['product_id'] === $product->id);

        if ($existingIndex !== false) {
            $currentQuantity = (int) ($this->cart[$existingIndex]['quantity'] ?: 0);

            if ($currentQuantity >= $available) {
                $this->cart[$existingIndex]['quantity'] = (string) $available;

                session()->flash('warning', __('keywords.low_stock_warning').': '.$product->name.' ('.__('keywords.available').': '.$product->quantity.')');

                return;
            }

            $this->cart[$existingIndex]['quantity'] = (string) ($currentQuantity + 1);

            return;
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => $product->category?->name ?? __('keywords.not_specified'),
            'cost_price' => (string) $product->cost_price,
            'sell_price' => (string) $product->cost_price,
            'available_quantity' => $available,
            'quantity' => '1',
        ];
    }

    public function removeFromCart(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        array_splice($this->cart, $index, 1);
        $this->cart = array_values($this->cart);
    }

    public function changeQuantity(int $index, int $delta): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $current = (int) ($this->cart[$index]['quantity'] ?: 0);
        $available = (int) $this->cart[$index]['available_quantity'];

        $next = $current + $delta;

        if ($next <= 0) {
            $this->removeFromCart($index);

            return;
        }

        if ($next > $available) {
            if ($available <= 0) {
                $productName = $this->cart[$index]['product_name'];
                $this->removeFromCart($index);
                session()->flash('warning', __('keywords.out_of_stock_warning').': '.$productName);

                return;
            }

            $this->cart[$index]['quantity'] = (string) $available;

            session()->flash(
                'warning',
                __('keywords.low_stock_warning').': '.
                $this->cart[$index]['product_name'].
                ' ('.__('keywords.available').': '.$available.')'
            );

            return;
        }

        $this->cart[$index]['quantity'] = (string) $next;
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function openCreateCustomerModal(): void
    {
        $this->newCustomer = [
            'name' => '',
            'place_id' => '',
            'phone' => '',
            'national_number' => '',
            'address' => '',
        ];

        $this->dispatch('open-modal-create-customer-inline');
    }

    public function createCustomerInline(): void
    {
        $request = new \App\Http\Requests\Customers\CreateCustomerRequest;

        $validated = $this->validate(
            collect($request->rules())->mapWithKeys(fn ($rules, $key) => ["newCustomer.{$key}" => $rules])->toArray(),
            $request->messages(),
            collect($request->attributes())->mapWithKeys(fn ($label, $key) => ["newCustomer.{$key}" => $label])->toArray()
        );

        $customer = Customer::create($validated['newCustomer']);

        $this->customer_id = $customer->id;
        $this->customerSearch = $customer->name;
        $this->dispatch('close-modal-create-customer-inline');
        $this->dispatch('open-modal-sale-payment');
    }

    public function selectCustomer(int $customerId, string $customerName): void
    {
        $this->customer_id = $customerId;
        $this->customerSearch = $customerName;
    }

    public function openPaymentModal(): void
    {
        if (count($this->cart) === 0) {
            $this->addError('cart', __('keywords.select_at_least_one_item'));

            return;
        }

        $this->dispatch('open-modal-sale-payment');
    }

    // ==========================================
    // COMPUTED PROPERTIES - Using SalePriceCalculator
    // ==========================================

    #[Computed]
    public function calculator(): SalePriceCalculator
    {
        return $this->getSaleCalculator($this->available_customer_credit);
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
    public function grandTotal(): float
    {
        return $this->calculator->grandTotal();
    }

    #[Computed]
    public function totalPrice(): float
    {
        return $this->grandTotal;
    }

    #[Computed]
    public function cartCount(): float
    {
        return (float) collect($this->cart)->sum(fn ($item) => (float) ($item['quantity'] ?? 0));
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        if (! $this->customer_id) {
            return null;
        }

        return Customer::find($this->customer_id);
    }

    #[Computed]
    public function availableCustomerCredit(): float
    {
        return $this->selectedCustomer?->available_credit ?? 0;
    }

    #[Computed]
    public function appliedCustomerCredit(): float
    {
        return min($this->subtotalAfterVat, $this->availableCustomerCredit);
    }

    #[Computed]
    public function cashAmountDue(): float
    {
        return $this->calculator->cashAmountDue();
    }

    #[Computed]
    public function remainingAfterDownPayment(): float
    {
        return $this->calculator->remainingAfterDownPayment();
    }

    // ==========================================
    // SAVE ACTION - Using FormRequest Validation
    // ==========================================

    public function save(CreateSaleAction $action): void
    {
        $request = new \App\Http\Requests\Sales\CreateSaleRequest;
        $request->merge($this->all());

        $validated = $this->validate($request->rules(), $request->messages(), $request->attributes());

        $sale = $action->execute($validated);

        session()->flash('success', __('keywords.sale_created'));

        if ($this->printAfterSave && $sale->number) {
            $this->redirect(route('sales.print', $sale->number), navigate: true);
        } else {
            $this->redirect(route('sales.create'), navigate: true);
        }
    }

    private function getSaleData(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'payment_type' => $this->payment_type,
            'down_payment' => $this->down_payment,
            'installment_months' => $this->installment_months,
            'interest_rate' => $this->interest_rate,
            'discount' => $this->discount,
            'with_vat' => $this->with_vat,
            'dealer_name' => $this->dealer_name,
            'cart' => $this->cart,
            'includeWaterReading' => $this->includeWaterReading,
            'water_filter_id' => $this->water_filter_id,
            'createNewFilter' => $this->createNewFilter,
            'newFilter' => $this->newFilter,
            'waterReading' => $this->waterReading,
        ];
    }

    // ==========================================
    // RENDER WITH COMPUTED PROPERTIES FOR DATA
    // ==========================================

    #[Computed]
    public function products()
    {
        $query = Product::query()->with('category')->orderBy('name');

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->activeCategory !== 'all') {
            $query->where('category_id', $this->activeCategory);
        }

        return $query->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function customers()
    {
        $query = Customer::query()->orderBy('name');

        if (filled($this->customerSearch)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->customerSearch.'%')
                    ->orWhere('phone', 'like', '%'.$this->customerSearch.'%')
                    ->orWhere('national_number', 'like', '%'.$this->customerSearch.'%');
            });
        }

        return $query->limit(100)->pluck('name', 'id')->all();
    }

    #[Computed]
    public function placeOptions(): array
    {
        return Place::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function customerFilters(): array
    {
        if (! $this->customer_id) {
            return [];
        }

        return WaterFilter::where('customer_id', $this->customer_id)
            ->orderBy('filter_model')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'label' => $f->filter_model.' - '.$f->address,
                'filter_model' => $f->filter_model,
                'address' => $f->address,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.sales.sale-create', [
            'products' => $this->products,
            'categories' => $this->categories,
            'customers' => $this->customers,
            'waterQualityOptions' => WaterQualityTypeEnum::cases(),
            'customerFilters' => $this->customerFilters,
        ]);
    }
}
