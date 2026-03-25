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
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'pos'])]
class SaleCreate extends Component
{
    private const VAT_RATE = 14;

    private const INSTALLMENT_MONTHLY_SURCHARGE = 100;

    public ?int $customer_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public string $interest_rate = '0';

    public bool $with_vat = false;

    public string $customerSearch = '';

    public ?string $dealer_name = null;

    public string $search = '';

    public string $activeCategory = 'all';

    public bool $includeWaterReading = false;

    public bool $printAfterSave = false;

    public array $cart = [];

    public array $newCustomer = [
        'name' => '',
        'phone' => '',
        'national_number' => '',
        'address' => '',
    ];

    public ?int $water_filter_id = null;

    public string $filterSearch = '';

    public bool $createNewFilter = false;

    public array $newFilter = [
        'filter_model' => '',
        'address' => '',
    ];

    public array $waterReading = [
        'technician_name' => '',
        'tds' => '',
        'water_quality' => '',
        'before_installment' => false,
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

    public function getCustomerFiltersProperty(): array
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
            $this->cart[$existingIndex]['quantity'] = (string) ($currentQuantity + 1);

            if (($currentQuantity + 1) > (int) $product->quantity) {
                session()->flash('warning', __('keywords.low_stock_warning').': '.$product->name.' ('.__('keywords.available').': '.$product->quantity.')');
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

        if ((int) $product->quantity <= 0) {
            session()->flash('warning', __('keywords.out_of_stock_warning').': '.$product->name);
        }
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

        $this->cart[$index]['quantity'] = (string) $next;

        if ($next > (int) $this->cart[$index]['available_quantity']) {
            session()->flash('warning', __('keywords.low_stock_warning').': '.$this->cart[$index]['product_name'].' ('.__('keywords.available').': '.$this->cart[$index]['available_quantity'].')');
        }
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

    public function getBaseTotalProperty(): float
    {
        return collect($this->cart)->sum(function ($item) {
            return ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0));
        });
    }

    public function getVatAmountProperty(): float
    {
        if (! $this->with_vat) {
            return 0;
        }

        return round($this->base_total * (self::VAT_RATE / 100), 2);
    }

    public function getSubtotalAfterVatProperty(): float
    {
        return $this->base_total + $this->vat_amount;
    }

    public function getTotalPriceProperty(): float
    {
        return $this->grand_total;
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
        return min($this->subtotal_after_vat, $this->available_customer_credit);
    }

    public function getCashAmountDueProperty(): float
    {
        if ($this->payment_type === 'installment') {
            return min((float) ($this->down_payment ?: 0), max(0, $this->subtotal_after_vat - $this->applied_customer_credit));
        }

        return max(0, $this->subtotal_after_vat - $this->applied_customer_credit);
    }

    public function getRemainingAfterDownPaymentProperty(): float
    {
        return max(0, $this->subtotal_after_vat - $this->applied_customer_credit - $this->cash_amount_due);
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

    public function getGrandTotalProperty(): float
    {
        if ($this->payment_type !== 'installment') {
            return $this->subtotal_after_vat;
        }

        return $this->subtotal_after_vat + $this->interest_amount + $this->installment_months_surcharge_total;
    }

    protected function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'dealer_name' => 'nullable|string|max:255',
            'includeWaterReading' => 'boolean',
            'with_vat' => 'boolean',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'interest_rate' => 'required_if:payment_type,installment|nullable|numeric|min:0|max:100',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.sell_price' => 'required|numeric|min:0.01',
            'cart.*.quantity' => 'required|integer|min:1',
        ];

        if ($this->includeWaterReading) {
            if ($this->createNewFilter) {
                $rules['newFilter.filter_model'] = 'required|string|max:255';
                $rules['newFilter.address'] = 'required|string|max:255';
            } else {
                $rules['water_filter_id'] = 'required|exists:water_filters,id';
            }
            $rules['waterReading.technician_name'] = 'required|string|max:255';
            $rules['waterReading.tds'] = 'required|numeric|min:0';
            $rules['waterReading.water_quality'] = 'required|in:'.implode(',', WaterQualityTypeEnum::values());
            $rules['waterReading.before_installment'] = 'boolean';
        } else {
            $rules['waterReading.technician_name'] = 'nullable|string|max:255';
            $rules['waterReading.tds'] = 'nullable|numeric|min:0';
            $rules['waterReading.water_quality'] = 'nullable|in:'.implode(',', WaterQualityTypeEnum::values());
        }

        foreach ($this->cart as $i => $item) {
            $rules["cart.{$i}.quantity"] = 'required|integer|min:1';
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'with_vat' => __('keywords.apply_vat'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'interest_rate' => __('keywords.interest_rate'),
            'dealer_name' => __('keywords.dealer_name'),
            'water_filter_id' => __('keywords.filter'),
            'newFilter.filter_model' => __('keywords.filter_model'),
            'newFilter.address' => __('keywords.address'),
            'waterReading.technician_name' => __('keywords.technician_name'),
            'waterReading.tds' => __('keywords.tds'),
            'waterReading.water_quality' => __('keywords.water_quality'),
            'waterReading.before_installment' => __('keywords.before_installment'),
        ];

        foreach ($this->cart as $i => $item) {
            $n = $i + 1;
            $attrs["cart.{$i}.sell_price"] = __('keywords.sell_price')." #{$n}";
            $attrs["cart.{$i}.quantity"] = __('keywords.quantity')." #{$n}";
        }

        return $attrs;
    }

    public function save(): void
    {
        $this->validate();

        $customer = Customer::findOrFail($this->customer_id);
        $subtotalAfterVat = $this->subtotal_after_vat;
        $isInstallment = $this->payment_type === 'installment';
        $months = $isInstallment ? (int) $this->installment_months : null;
        $interestRate = $isInstallment ? max(0, (float) ($this->interest_rate ?: 0)) : null;
        $appliedCredit = min($customer->available_credit, $subtotalAfterVat);
        $downPayment = $isInstallment
            ? min((float) $this->down_payment, max(0, $subtotalAfterVat - $appliedCredit))
            : max(0, $subtotalAfterVat - $appliedCredit);
        $installmentBase = max(0, $subtotalAfterVat - $appliedCredit - $downPayment);
        $interestAmount = $isInstallment
            ? round($installmentBase * (($interestRate ?? 0) / 100), 2)
            : 0;
        $installmentMonthsSurchargeTotal = $isInstallment && $months >= 3
            ? $months * self::INSTALLMENT_MONTHLY_SURCHARGE
            : 0;
        $installmentAmount = $isInstallment && $months > 0
            ? round(($installmentBase + $interestAmount + $installmentMonthsSurchargeTotal) / $months, 2)
            : null;
        $totalPrice = $subtotalAfterVat + $interestAmount + $installmentMonthsSurchargeTotal;

        $saleNumber = null;

        DB::transaction(function () use ($customer, $totalPrice, $isInstallment, $downPayment, $months, $installmentAmount, $appliedCredit, $interestRate, &$saleNumber) {
            $sale = Sale::create([
                'dealer_name' => $this->dealer_name ?? null,
                'user_name' => auth()->user()->name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'interest_rate' => $interestRate,
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'with_vat' => $this->with_vat,
                'user_id' => auth()->id(),
                'customer_id' => $customer->id,
            ]);

            $saleNumber = $sale->number;

            foreach ($this->cart as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                // Allow sales even if stock is zero or negative
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
                $filterId = $this->water_filter_id;

                if ($this->createNewFilter) {
                    $newFilter = WaterFilter::create([
                        'filter_model' => $this->newFilter['filter_model'],
                        'address' => $this->newFilter['address'],
                        'customer_id' => $customer->id,
                    ]);
                    $filterId = $newFilter->id;
                }

                WaterReading::create([
                    'technician_name' => $this->waterReading['technician_name'],
                    'tds' => $this->waterReading['tds'],
                    'water_quality' => $this->waterReading['water_quality'],
                    'before_installment' => $this->waterReading['before_installment'] ?? false,
                    'water_filter_id' => $filterId,
                ]);
            }
        });

        session()->flash('success', __('keywords.sale_created'));

        if ($this->printAfterSave && $saleNumber) {
            $this->redirect(route('sales.print', $saleNumber), navigate: true);
        } else {
            $this->redirect(route('sales.create'), navigate: true);
        }
    }

    public function render()
    {
        $productsQuery = Product::query()->with('category')->orderBy('name');
        $customersQuery = Customer::query()->orderBy('name');

        if (filled($this->search)) {
            $productsQuery->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->activeCategory !== 'all') {
            $productsQuery->where('category_id', $this->activeCategory);
        }

        if (filled($this->customerSearch)) {
            $customersQuery->where(function ($query) {
                $query->where('name', 'like', '%'.$this->customerSearch.'%')
                    ->orWhere('phone', 'like', '%'.$this->customerSearch.'%')
                    ->orWhere('national_number', 'like', '%'.$this->customerSearch.'%');
            });
        }

        return view('livewire.sales.sale-create', [
            'products' => $productsQuery->get(),
            'categories' => Category::orderBy('name')->get(),
            'customers' => $customersQuery->limit(100)->pluck('name', 'id')->all(),
            'waterQualityOptions' => WaterQualityTypeEnum::cases(),
            'customerFilters' => $this->customer_filters,
        ]);
    }
}
