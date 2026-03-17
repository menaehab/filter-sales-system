<?php

namespace App\Livewire\Sales;

use App\Enums\WaterQualityTypeEnum;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WaterReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app',['title' => 'pos'])]
class SaleCreate extends Component
{
    public ?int $customer_id = null;
    public string $payment_type = 'cash';
    public string $down_payment = '0';
    public string $installment_months = '';
    public string $customerSearch = '';
    public ?string $dealer_name = null;

    public string $search = '';
    public string $activeCategory = 'all';
    public bool $includeWaterReading = false;

    public array $cart = [];

    public array $newCustomer = [
        'name' => '',
        'phone' => '',
        'national_number' => '',
        'address' => '',
    ];

    public array $waterReading = [
        'technician_name' => '',
        'tds' => '',
        'water_quality' => '',
    ];

    public function updatedPaymentType(string $value): void
    {
        if ($value !== 'installment') {
            $this->down_payment = '0';
            $this->installment_months = '';
        }
    }

    public function updatedIncludeWaterReading(bool $value): void
    {
        if (! $value) {
            $this->waterReading = [
                'technician_name' => '',
                'tds' => '',
                'water_quality' => '',
            ];
        }
    }

    public function setActiveCategory(string $categoryId): void
    {
        $this->activeCategory = $categoryId;
    }

    public function addToCart(int $productId): void
    {
        $product = Product::with('category')->findOrFail($productId);

        $existingIndex = collect($this->cart)->search(fn ($item) => (int) $item['product_id'] === $product->id);

        if ($existingIndex !== false) {
            $currentQuantity = (int) ($this->cart[$existingIndex]['quantity'] ?: 0);
            if ($currentQuantity < (int) $product->quantity) {
                $this->cart[$existingIndex]['quantity'] = (string) ($currentQuantity + 1);
            }

            return;
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => $product->category?->name ?? __('keywords.not_specified'),
            'cost_price' => (string) $product->cost_price,
            'sell_price' => (string) $product->cost_price,
            'available_quantity' => (int) $product->quantity,
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

        $next = (int) ($this->cart[$index]['quantity'] ?: 0) + $delta;

        if ($next <= 0) {
            $this->removeFromCart($index);
            return;
        }

        $this->cart[$index]['quantity'] = (string) min($next, (int) $this->cart[$index]['available_quantity']);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function openCreateCustomerModal(): void
    {
        $this->newCustomer = [
            'name' => '',
            'phone' => '',
            'national_number' => '',
            'address' => '',
        ];

        $this->dispatch('open-modal-create-customer-inline');
    }

    public function createCustomerInline(): void
    {
        $this->validate([
            'newCustomer.name' => ['required', 'string', 'max:255'],
            'newCustomer.phone' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
            'newCustomer.national_number' => ['nullable', 'string', 'max:14', 'min:14'],
            'newCustomer.address' => ['nullable', 'string', 'max:255'],
        ], [], [
            'newCustomer.name' => __('keywords.name'),
            'newCustomer.phone' => __('keywords.phone'),
            'newCustomer.national_number' => __('keywords.national_number'),
            'newCustomer.address' => __('keywords.address'),
        ]);

        $customer = Customer::create([
            'name' => $this->newCustomer['name'],
            'phone' => $this->newCustomer['phone'] ?: null,
            'national_number' => $this->newCustomer['national_number'] ?: null,
            'address' => $this->newCustomer['address'] ?: null,
        ]);

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

    public function getTotalPriceProperty(): float
    {
        return collect($this->cart)->sum(function ($item) {
            return ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0));
        });
    }

    public function getCartCountProperty(): float
    {
        return (float) collect($this->cart)->sum(fn ($item) => (float) ($item['quantity'] ?: 0));
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        if (! $this->customer_id) {
            return null;
        }

        return Customer::find($this->customer_id);
    }

    public function getAvailableCustomerCreditProperty(): float
    {
        return $this->selectedCustomer?->available_credit ?? 0;
    }

    public function getAppliedCustomerCreditProperty(): float
    {
        return min($this->total_price, $this->available_customer_credit);
    }

    public function getCashAmountDueProperty(): float
    {
        if ($this->payment_type === 'installment') {
            return min((float) ($this->down_payment ?: 0), max(0, $this->total_price - $this->applied_customer_credit));
        }

        return max(0, $this->total_price - $this->applied_customer_credit);
    }

    public function getRemainingAfterDownPaymentProperty(): float
    {
        return max(0, $this->total_price - $this->applied_customer_credit - $this->cash_amount_due);
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
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'dealer_name' => 'nullable|string|max:255',
            'includeWaterReading' => 'boolean',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.sell_price' => 'required|numeric|min:0.01',
            'cart.*.quantity' => 'required|integer|min:1',
        ];

        if ($this->includeWaterReading) {
            $rules['waterReading.technician_name'] = 'required|string|max:255';
            $rules['waterReading.tds'] = 'required|numeric|min:0';
            $rules['waterReading.water_quality'] = 'required|in:' . implode(',', WaterQualityTypeEnum::values());
        } else {
            $rules['waterReading.technician_name'] = 'nullable|string|max:255';
            $rules['waterReading.tds'] = 'nullable|numeric|min:0';
            $rules['waterReading.water_quality'] = 'nullable|in:' . implode(',', WaterQualityTypeEnum::values());
        }

        foreach ($this->cart as $i => $item) {
            $rules["cart.{$i}.quantity"] = "required|integer|min:1|max:{$item['available_quantity']}";
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'dealer_name' => __('keywords.dealer_name'),
            'waterReading.technician_name' => __('keywords.technician_name'),
            'waterReading.tds' => __('keywords.tds'),
            'waterReading.water_quality' => __('keywords.water_quality'),
        ];

        foreach ($this->cart as $i => $item) {
            $n = $i + 1;
            $attrs["cart.{$i}.sell_price"] = __('keywords.sell_price') . " #{$n}";
            $attrs["cart.{$i}.quantity"] = __('keywords.quantity') . " #{$n}";
        }

        return $attrs;
    }

    public function save(): void
    {
        $this->validate();

        foreach ($this->cart as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || (int) $product->quantity < (int) $item['quantity']) {
                throw ValidationException::withMessages([
                    'cart' => __('keywords.not_available') . ': ' . ($item['product_name'] ?? __('keywords.product')),
                ]);
            }
        }

        $customer = Customer::findOrFail($this->customer_id);
        $totalPrice = $this->total_price;
        $isInstallment = $this->payment_type === 'installment';
        $appliedCredit = min($customer->available_credit, $totalPrice);
        $downPayment = $isInstallment
            ? min((float) $this->down_payment, max(0, $totalPrice - $appliedCredit))
            : max(0, $totalPrice - $appliedCredit);
        $months = $isInstallment ? (int) $this->installment_months : null;
        $installmentAmount = $isInstallment && $months > 0
            ? round(max(0, $totalPrice - $appliedCredit - $downPayment) / $months, 2)
            : null;

        DB::transaction(function () use ($customer, $totalPrice, $isInstallment, $downPayment, $months, $installmentAmount, $appliedCredit) {
            $sale = Sale::create([
                'dealer_name' => $this->dealer_name ?? null,
                'user_name' => auth()->user()->name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'user_id' => auth()->id(),
                'customer_id' => $customer->id,
            ]);

            foreach ($this->cart as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ((int) $product->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => __('keywords.not_available') . ': ' . $product->name,
                    ]);
                }

                SaleItem::create([
                    'sell_price' => (float) $item['sell_price'],
                    'cost_price' => (float) $item['cost_price'],
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

            if ($downPayment > 0) {
                $payment = CustomerPayment::create([
                    'amount' => $downPayment,
                    'payment_method' => 'cash',
                    'note' => $isInstallment ? __('keywords.down_payment') : __('keywords.cash_payment'),
                    'customer_id' => $customer->id,
                    'user_id' => auth()->id(),
                ]);

                CustomerPaymentAllocation::create([
                    'amount' => $downPayment,
                    'customer_payment_id' => $payment->id,
                    'sale_id' => $sale->id,
                ]);
            }

            if ($appliedCredit > 0) {
                $creditPayment = CustomerPayment::create([
                    'amount' => $appliedCredit,
                    'payment_method' => 'customer_credit',
                    'note' => __('keywords.applied_customer_credit'),
                    'customer_id' => $customer->id,
                    'user_id' => auth()->id(),
                ]);

                CustomerPaymentAllocation::create([
                    'amount' => $appliedCredit,
                    'customer_payment_id' => $creditPayment->id,
                    'sale_id' => $sale->id,
                ]);
            }

            if ($this->includeWaterReading) {
                WaterReading::create([
                    'technician_name' => $this->waterReading['technician_name'],
                    'tds' => $this->waterReading['tds'],
                    'water_quality' => $this->waterReading['water_quality'],
                    'customer_id' => $customer->id,
                ]);
            }
        });

        session()->flash('success', __('keywords.sale_created'));
        $this->redirect(route('sales'), navigate: true);
    }

    public function render()
    {
        $productsQuery = Product::query()->with('category')->orderBy('name');
        $customersQuery = Customer::query()->orderBy('name');

        if (filled($this->search)) {
            $productsQuery->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->activeCategory !== 'all') {
            $productsQuery->where('category_id', $this->activeCategory);
        }

        if (filled($this->customerSearch)) {
            $customersQuery->where(function ($query) {
                $query->where('name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('national_number', 'like', '%' . $this->customerSearch . '%');
            });
        }

        return view('livewire.sales.sale-create', [
            'products' => $productsQuery->get(),
            'categories' => Category::orderBy('name')->get(),
            'customers' => $customersQuery->limit(100)->pluck('name', 'id')->all(),
            'waterQualityOptions' => WaterQualityTypeEnum::cases(),
        ]);
    }
}
