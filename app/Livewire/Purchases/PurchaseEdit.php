<?php

namespace App\Livewire\Purchases;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseEdit extends Component
{
    public Purchase $purchase;

    public ?int $supplier_id = null;
    public string $payment_type = 'cash';
    public string $down_payment = '0';
    public string $installment_months = '';

    public array $items = [];
    public array $newSupplier = [
        'name' => '',
        'phone' => '',
    ];
    public array $newCategory = [
        'name' => '',
    ];
    public array $newProduct = [
        'name' => '',
        'description' => '',
        'cost_price' => '',
        'min_quantity' => '0',
        'category_id' => '',
    ];
    public ?int $targetItemIndexForNewProduct = null;

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase->load(['items', 'paymentAllocations.supplierPayment']);

        $this->supplier_id = $purchase->supplier_id;
        $this->payment_type = $purchase->isInstallment() ? 'installment' : 'cash';
        $this->down_payment = (string) $purchase->down_payment;
        $this->installment_months = (string) ($purchase->installment_months ?? '');

        $this->items = $purchase->items->map(fn($item) => [
            'product_id' => (string) $item->product_id,
            'product_name' => $item->product_name,
            'cost_price' => (string) $item->cost_price,
            'quantity' => (string) $item->quantity,
        ])->toArray();

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'cost_price' => '',
            'quantity' => '1',
        ];
    }

    public function removeItem(int $index)
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'product_id') {
            $index = (int) $parts[0];
            $productId = $value;
            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->items[$index]['product_name'] = $product->name;
                    $this->items[$index]['cost_price'] = (string) $product->cost_price;
                }
            }
        }
    }

    public function openCreateSupplierModal(): void
    {
        $this->newSupplier = [
            'name' => '',
            'phone' => '',
        ];

        $this->dispatch('open-modal-create-supplier-inline');
    }

    public function createSupplierInline(): void
    {
        $this->validate([
            'newSupplier.name' => ['required', 'string', 'max:255'],
            'newSupplier.phone' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
        ], [], [
            'newSupplier.name' => __('keywords.name'),
            'newSupplier.phone' => __('keywords.phone'),
        ]);

        $supplier = Supplier::create([
            'name' => $this->newSupplier['name'],
            'phone' => $this->newSupplier['phone'] ?: null,
        ]);

        $this->supplier_id = $supplier->id;
        $this->dispatch('close-modal-create-supplier-inline');
    }

    public function openCreateProductModal(?int $index = null): void
    {
        $this->targetItemIndexForNewProduct = $index;

        $this->newProduct = [
            'name' => '',
            'description' => '',
            'cost_price' => '',
            'min_quantity' => '0',
            'category_id' => '',
        ];

        $this->dispatch('open-modal-create-product-inline');
    }

    public function createCategoryInline(): void
    {
        $this->validate([
            'newCategory.name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ], [], [
            'newCategory.name' => __('keywords.category'),
        ]);

        $category = Category::create([
            'name' => $this->newCategory['name'],
        ]);

        $this->newCategory['name'] = '';
        $this->newProduct['category_id'] = (string) $category->id;

        $this->dispatch('close-modal-create-category-inline');
    }

    public function createProductInline(): void
    {
        $this->validate([
            'newProduct.name' => ['required', 'string', 'max:255'],
            'newProduct.cost_price' => ['required', 'numeric', 'min:0'],
            'newProduct.min_quantity' => ['required', 'integer', 'min:0'],
            'newProduct.description' => ['nullable', 'string'],
            'newProduct.category_id' => ['required', 'exists:categories,id'],
        ], [], [
            'newProduct.name' => __('keywords.name'),
            'newProduct.cost_price' => __('keywords.cost_price'),
            'newProduct.min_quantity' => __('keywords.min_quantity'),
            'newProduct.description' => __('keywords.description'),
            'newProduct.category_id' => __('keywords.category'),
        ]);

        $product = Product::create([
            'name' => $this->newProduct['name'],
            'description' => $this->newProduct['description'] ?: null,
            'cost_price' => (float) $this->newProduct['cost_price'],
            'min_quantity' => (int) $this->newProduct['min_quantity'],
            'quantity' => 0,
            'category_id' => (int) $this->newProduct['category_id'],
        ]);

        if ($this->targetItemIndexForNewProduct !== null && isset($this->items[$this->targetItemIndexForNewProduct])) {
            $this->items[$this->targetItemIndexForNewProduct]['product_id'] = (string) $product->id;
            $this->items[$this->targetItemIndexForNewProduct]['product_name'] = $product->name;
            $this->items[$this->targetItemIndexForNewProduct]['cost_price'] = (string) $product->cost_price;
        }

        $this->dispatch('close-modal-create-product-inline');
    }

    public function getTotalPriceProperty(): float
    {
        return collect($this->items)->sum(function ($item) {
            return ((float) ($item['cost_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0));
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
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,installment',
            'down_payment' => 'required_if:payment_type,installment|numeric|min:0',
            'installment_months' => 'required_if:payment_type,installment|nullable|integer|min:1|max:60',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cost_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'supplier_id' => __('keywords.supplier'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
        ];

        foreach ($this->items as $i => $item) {
            $n = $i + 1;
            $attrs["items.{$i}.product_id"] = __('keywords.product') . " #{$n}";
            $attrs["items.{$i}.cost_price"] = __('keywords.cost_price') . " #{$n}";
            $attrs["items.{$i}.quantity"] = __('keywords.quantity') . " #{$n}";
        }

        return $attrs;
    }

    public function update()
    {
        $this->validate();

        $supplier = Supplier::findOrFail($this->supplier_id);
        $totalPrice = $this->total_price;
        $isInstallment = $this->payment_type === 'installment';
        $downPayment = $isInstallment ? (float) $this->down_payment : $totalPrice;
        $months = $isInstallment ? (int) $this->installment_months : null;
        $installmentAmount = $isInstallment ? $this->installment_amount : null;

        DB::transaction(function () use ($supplier, $totalPrice, $isInstallment, $downPayment, $months, $installmentAmount) {
            // Reverse old stock changes
            foreach ($this->purchase->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->decrement('quantity', $oldItem->quantity);
                }
            }

            // Delete old items and movements
            ProductMovement::where('movable_type', Purchase::class)
                ->where('movable_id', $this->purchase->id)
                ->delete();
            $this->purchase->items()->delete();

            $this->purchase->update([
                'supplier_name' => $supplier->name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'supplier_id' => $supplier->id,
            ]);

            // Create new items
            foreach ($this->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                PurchaseItem::create([
                    'product_name' => $product->name,
                    'cost_price' => (float) $item['cost_price'],
                    'quantity' => (int) $item['quantity'],
                    'purchase_id' => $this->purchase->id,
                    'product_id' => $product->id,
                ]);

                // Keep product cost price synced with latest purchase cost.
                $product->update([
                    'cost_price' => (float) $item['cost_price'],
                ]);

                $product->increment('quantity', (int) $item['quantity']);

                ProductMovement::create([
                    'quantity' => (int) $item['quantity'],
                    'movable_type' => Purchase::class,
                    'movable_id' => $this->purchase->id,
                    'product_id' => $product->id,
                ]);
            }
        });

        return $this->redirect(route('purchases'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-edit', [
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id')->all(),
            'products' => Product::orderBy('name')->pluck('name', 'id')->all(),
            'categories' => Category::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
