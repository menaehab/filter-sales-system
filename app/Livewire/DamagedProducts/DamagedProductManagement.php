<?php

namespace App\Livewire\DamagedProducts;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'damaged_products_management'])]
class DamagedProductManagement extends Component
{
    use WithSearchAndPagination, HasForm, HasCrudModals, HasCrudQuery, HasValidationAttributes;

    public $productSlug = '';
    public $productSearch = '';
    public $products;
    public $formProductSearch = '';

    public function mount()
    {
        $this->resetForm();
        $this->products = Product::with('category')->orderBy('name')->get();

        if ($this->productSlug) {
            $this->productSearch = $this->products->firstWhere('slug', $this->productSlug)?->name ?? '';
        }
    }

    public function getDamagedProductsProperty()
    {
        return $this->items;
    }

    protected function getModelClass(): string
    {
        return DamagedProduct::class;
    }

    protected function rules()
    {
        $maxQuantity = $this->form['product_id']
            ? Product::find($this->form['product_id'])?->quantity ?? 0
            : 0;

        if ($this->editId) {
            $existingDamage = DamagedProduct::find($this->editId);
            if ($existingDamage && $existingDamage->product_id == $this->form['product_id']) {
                $maxQuantity += $existingDamage->quantity;
            }
        }

        return [
            'form.product_id' => ['required', 'exists:products,id'],
            'form.quantity' => ['required', 'integer', 'min:1', 'max:' . $maxQuantity],
            'form.reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.product_id' => __('keywords.product'),
            'form.quantity' => __('keywords.quantity'),
            'form.reason' => __('keywords.reason'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'product_id' => null,
            'quantity' => null,
            'reason' => '',
        ];
    }

    protected function additionalQueryString(): array
    {
        return [
            'productSlug' => ['as' => 'product', 'except' => ''],
        ];
    }

    public function updatingProductSlug()
    {
        $this->resetPage();

        $this->productSearch = $this->products->firstWhere('slug', $this->productSlug)?->name ?? '';
    }

    protected function applyAdditionalFilters($query): void
    {
        if ($this->productSlug) {
            $query->whereHas('product', fn ($q) => $q->where('slug', $this->productSlug));
        }
    }

    public function create()
    {
        $this->authorizeManageDamagedProducts();

        $this->validate();

        DB::transaction(function () {
            $product = Product::findOrFail($this->form['product_id']);

            $damage = DamagedProduct::create([
                'product_id' => $this->form['product_id'],
                'cost_price' => $product->cost_price,
                'quantity' => $this->form['quantity'],
                'reason' => $this->form['reason'],
                'user_id' => auth()->id(),
            ]);

            $product->decrement('quantity', $this->form['quantity']);

            ProductMovement::create([
                'quantity' => -$this->form['quantity'],
                'movable_type' => DamagedProduct::class,
                'movable_id' => $damage->id,
                'product_id' => $this->form['product_id'],
            ]);
        });

        $this->products = Product::with('category')->orderBy('name')->get();

        $this->resetForm();
        $this->formProductSearch = '';
        $this->dispatch('close-modal-create-damaged-product');
        $this->resetPage();
    }

    public function openEdit($id)
    {
        $this->authorizeManageDamagedProducts();

        $damagedProduct = DamagedProduct::with('product')->findOrFail($id);

        $this->editId = $damagedProduct->id;

        $this->form = [
            'product_id' => $damagedProduct->product_id,
            'quantity' => $damagedProduct->quantity,
            'reason' => $damagedProduct->reason,
        ];

        $this->formProductSearch = $damagedProduct->product->name;

        $this->dispatch('open-modal-edit-damaged-product');
    }

    public function updateDamagedProduct()
    {
        $this->authorizeManageDamagedProducts();

        $this->validate();

        DB::transaction(function () {
            $damagedProduct = DamagedProduct::findOrFail($this->editId);
            $oldQuantity = $damagedProduct->quantity;
            $oldProductId = $damagedProduct->product_id;

            $product = Product::findOrFail($this->form['product_id']);

            if ($oldProductId == $this->form['product_id']) {
                $quantityDiff = $this->form['quantity'] - $oldQuantity;
                $product->decrement('quantity', $quantityDiff);
            } else {
                Product::find($oldProductId)?->increment('quantity', $oldQuantity);
                $product->decrement('quantity', $this->form['quantity']);
            }

            $damagedProduct->update([
                'product_id' => $this->form['product_id'],
                'cost_price' => $product->cost_price,
                'quantity' => $this->form['quantity'],
                'reason' => $this->form['reason'],
            ]);

            ProductMovement::where('movable_type', DamagedProduct::class)
                ->where('movable_id', $damagedProduct->id)
                ->delete();

            ProductMovement::create([
                'quantity' => -$this->form['quantity'],
                'movable_type' => DamagedProduct::class,
                'movable_id' => $damagedProduct->id,
                'product_id' => $this->form['product_id'],
            ]);
        });

        $this->products = Product::with('category')->orderBy('name')->get();

        $this->resetForm();
        $this->formProductSearch = '';
        $this->editId = null;

        $this->dispatch('close-modal-edit-damaged-product');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->authorizeManageDamagedProducts();

        $this->openDeleteModal($id, 'open-modal-delete-damaged-product');
    }

    public function delete()
    {
        $this->authorizeManageDamagedProducts();

        DB::transaction(function () {
            $damagedProduct = DamagedProduct::find($this->deleteId);

            if ($damagedProduct) {
                $damagedProduct->product?->increment('quantity', $damagedProduct->quantity);

                ProductMovement::where('movable_type', DamagedProduct::class)
                    ->where('movable_id', $damagedProduct->id)
                    ->delete();

                $damagedProduct->delete();
            }
        });

        $this->products = Product::with('category')->orderBy('name')->get();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-damaged-product');
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['reason', 'product.name'];
    }

    protected function getWithRelations(): array
    {
        return ['product', 'user'];
    }

    public function render()
    {
        return view('livewire.damaged-products.damaged-product-management');
    }

    public function authorizeManageDamagedProducts()
    {
        return auth()->user()->can('manage_damaged_products');
    }
}
