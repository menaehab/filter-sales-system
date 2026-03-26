<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Enums\WaterQualityTypeEnum;
use App\Support\SalePriceCalculator;

trait HasSaleForm
{
    public ?int $customer_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public string $interest_rate = '0';

    public string $discount = '0';

    public bool $with_vat = false;

    public ?string $dealer_name = null;

    public bool $includeWaterReading = false;

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

    /** @var array<int, array{product_id: string, product_name: string, sell_price: string, cost_price: string, quantity: string, available_quantity?: int, category_name?: string}> */
    public array $cart = [];

    protected function saleFormRules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'interest_rate' => 'required_if:payment_type,installment|nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'with_vat' => 'boolean',
            'dealer_name' => 'nullable|string|max:255',
            'includeWaterReading' => 'boolean',
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
        }

        return $rules;
    }

    protected function saleFormValidationAttributes(): array
    {
        $attrs = [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'with_vat' => __('keywords.apply_vat'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'interest_rate' => __('keywords.interest_rate'),
            'discount' => __('keywords.discount'),
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

    public function isInstallmentPayment(): bool
    {
        return $this->payment_type === 'installment';
    }

    protected function resetInstallmentFields(): void
    {
        $this->down_payment = '0';
        $this->installment_months = '';
        $this->interest_rate = '0';
    }

    protected function resetWaterReadingFields(): void
    {
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

    public function addProductToCart(int $productId, string $productName, string $categoryName, float $costPrice, int $availableQuantity): bool
    {
        $existingIndex = collect($this->cart)->search(fn ($item) => (int) $item['product_id'] === $productId);

        if ($existingIndex !== false) {
            $currentQuantity = (int) ($this->cart[$existingIndex]['quantity'] ?? 0);
            $this->cart[$existingIndex]['quantity'] = (string) ($currentQuantity + 1);

            return ($currentQuantity + 1) <= $availableQuantity;
        }

        $this->cart[] = [
            'product_id' => (string) $productId,
            'product_name' => $productName,
            'category_name' => $categoryName,
            'cost_price' => (string) $costPrice,
            'sell_price' => (string) $costPrice,
            'available_quantity' => $availableQuantity,
            'quantity' => '1',
        ];

        return $availableQuantity > 0;
    }

    public function removeProductFromCart(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        array_splice($this->cart, $index, 1);
        $this->cart = array_values($this->cart);
    }

    public function clearProductCart(): void
    {
        $this->cart = [];
    }

    protected function getSaleCalculator(?float $appliedCredit = 0): SalePriceCalculator
    {
        $calculator = SalePriceCalculator::make()
            ->withItems($this->cart)
            ->withDiscount((float) ($this->discount ?? 0))
            ->withVat($this->with_vat)
            ->withAppliedCredit($appliedCredit);

        if ($this->isInstallmentPayment()) {
            $calculator->withInstallment(
                (float) ($this->down_payment ?? 0),
                (int) ($this->installment_months ?? 0),
                (float) ($this->interest_rate ?? 0)
            );
        }

        return $calculator;
    }
}
