<?php

namespace App\Livewire\Purchases;

use App\Actions\Purchases\CreatePurchaseAction;
use App\Actions\Suppliers\CreateSupplierAction;
use App\Livewire\Traits\HasPhoneRepeater;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseCreate extends Component
{
    use HasPhoneRepeater;

    public ?int $supplier_id = null;

    public string $payment_type = 'cash';

    public string $down_payment = '0';

    public string $installment_months = '';

    public bool $printAfterSave = false;

    public string $created_at = '';

    public array $form = [];

    public array $items = [];

    public array $newSupplier = [
        'name' => '',
        'phones' => [['number' => '']],
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
        'for_maintenance' => false,
    ];

    public ?int $targetItemIndexForNewProduct = null;

    public function mount(): void
    {
        $this->addItem();
        $this->created_at = now()->format('Y-m-d\TH:i');
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    // ==========================================
    // ITEM MANAGEMENT
    // ==========================================

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
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
                    $this->items[$index]['cost_price'] = (string) $product->cost_price;
                }
            }
        }
    }

    // ==========================================
    // INLINE CREATE MODALS
    // ==========================================

    public function openCreateSupplierModal(): void
    {
        $this->newSupplier = ['name' => '', 'phones' => [['number' => '']]];
        $this->dispatch('open-modal-create-supplier-inline');
    }

    public function createSupplierInline(CreateSupplierAction $action): void
    {
        $this->validate([
            'newSupplier.name' => ['required', 'string', 'max:255'],
            'newSupplier.phones' => ['nullable', 'array'],
            'newSupplier.phones.*.number' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
        ], [], [
            'newSupplier.name' => __('keywords.name'),
            'newSupplier.phones.*.number' => __('keywords.phone'),
        ]);

        $supplier = $action->execute([
            'name' => $this->newSupplier['name'],
            'phones' => $this->newSupplier['phones'] ?? [],
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
            'for_maintenance' => false,
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

        $category = Category::create(['name' => $this->newCategory['name']]);

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
            'for_maintenance' => (bool) $this->newProduct['for_maintenance'],
        ]);

        if ($this->targetItemIndexForNewProduct !== null && isset($this->items[$this->targetItemIndexForNewProduct])) {
            $this->items[$this->targetItemIndexForNewProduct]['product_id'] = (string) $product->id;
            $this->items[$this->targetItemIndexForNewProduct]['product_name'] = $product->name;
            $this->items[$this->targetItemIndexForNewProduct]['cost_price'] = (string) $product->cost_price;
            $this->items[$this->targetItemIndexForNewProduct]['for_maintenance'] = $product->for_maintenance;
        }

        $this->dispatch('close-modal-create-product-inline');
    }

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================

    #[Computed]
    public function totalPrice(): float
    {
        return collect($this->items)->sum(function ($item) {
            return ((float) ($item['cost_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });
    }

    #[Computed]
    public function selectedSupplier(): ?Supplier
    {
        if (! $this->supplier_id) {
            return null;
        }

        return Supplier::find($this->supplier_id);
    }

    #[Computed]
    public function availableSupplierCredit(): float
    {
        return $this->selectedSupplier?->available_credit ?? 0;
    }

    #[Computed]
    public function appliedSupplierCredit(): float
    {
        return min($this->totalPrice, $this->availableSupplierCredit);
    }

    #[Computed]
    public function cashAmountDue(): float
    {
        if ($this->payment_type === 'installment') {
            return min((float) ($this->down_payment ?? 0), max(0, $this->totalPrice - $this->appliedSupplierCredit));
        }

        return max(0, $this->totalPrice - $this->appliedSupplierCredit);
    }

    #[Computed]
    public function remainingAfterDownPayment(): float
    {
        return max(0, $this->totalPrice - $this->appliedSupplierCredit - $this->cashAmountDue);
    }

    #[Computed]
    public function installmentAmount(): float
    {
        $months = (int) ($this->installment_months ?? 0);
        if ($months <= 0) {
            return 0;
        }

        return round($this->remainingAfterDownPayment / $months, 2);
    }

    #[Computed]
    public function suppliers(): array
    {
        return Supplier::orderBy('name')->pluck('name', 'id')->all();
    }

    #[Computed]
    public function products(): array
    {
        return Product::orderBy('name')->pluck('name', 'id')->all();
    }

    #[Computed]
    public function categories(): array
    {
        return Category::orderBy('name')->pluck('name', 'id')->all();
    }

    // ==========================================
    // SAVE ACTION
    // ==========================================

    public function save(CreatePurchaseAction $action): void
    {
        $request = new \App\Http\Requests\Purchases\CreatePurchaseRequest;

        // Temporarily add form property for validation
        $this->form = [
            'supplier_id' => $this->supplier_id,
            'payment_type' => $this->payment_type,
            'down_payment' => $this->down_payment,
            'installment_months' => $this->installment_months,
            'created_at' => $this->created_at,
            'items' => $this->items,
        ];

        $validated = $this->validate($request->rules(), $request->messages(), $request->attributes());

        $purchase = $action->execute($validated);

        if ($this->printAfterSave && $purchase->number) {
            $this->redirect(route('purchases.print', $purchase->number), navigate: true);

            return;
        }

        $this->redirect(route('purchases'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-create', [
            'suppliers' => $this->suppliers,
            'products' => $this->products,
            'categories' => $this->categories,
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
