<?php

namespace App\Livewire\Products;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, HasValidationAttributes, WithSearchAndPagination;

    public $categorySlug = '';

    public $stockStatus = '';

    public function mount()
    {
        $this->resetForm();
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    protected function rules()
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.cost_price' => ['required', 'numeric', 'min:0'],
            'form.quantity' => ['required', 'integer', 'min:0'],
            'form.min_quantity' => ['required', 'integer', 'min:0'],
            'form.description' => ['nullable', 'string'],
            'form.category_id' => ['required', 'exists:categories,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => __('keywords.name'),
            'form.cost_price' => __('keywords.cost_price'),
            'form.quantity' => __('keywords.quantity'),
            'form.min_quantity' => __('keywords.min_quantity'),
            'form.description' => __('keywords.description'),
            'form.category_id' => __('keywords.category'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'name' => '',
            'cost_price' => null,
            'quantity' => null,
            'min_quantity' => null,
            'description' => '',
            'category_id' => null,
        ];
    }

    protected function additionalQueryString(): array
    {
        return [
            'categorySlug' => ['as' => 'category', 'except' => ''],
            'stockStatus' => ['as' => 'stock', 'except' => ''],
        ];
    }

    public function updatingCategorySlug()
    {
        $this->resetPage();
    }

    public function updatingStockStatus()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorizeManageProducts();

        $this->validate();

        Product::create($this->form);

        $this->resetForm();
        $this->dispatch('close-modal-create-product');
        $this->resetPage();
    }

    public function openEdit($id)
    {
        $this->authorizeManageProducts();

        $product = Product::findOrFail($id);

        $this->editId = $product->id;

        $this->form = [
            'name' => $product->name,
            'cost_price' => $product->cost_price,
            'quantity' => $product->quantity,
            'min_quantity' => $product->min_quantity,
            'description' => $product->description,
            'category_id' => $product->category_id,
        ];

        $this->dispatch('open-modal-edit-product');
    }

    public function updateProduct()
    {
        $this->authorizeManageProducts();

        $this->validate();

        Product::findOrFail($this->editId)->update($this->form);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-product');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->authorizeManageProducts();

        $this->openDeleteModal($id, 'open-modal-delete-product');
    }

    public function delete()
    {
        $this->authorizeManageProducts();

        Product::find($this->deleteId)?->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-product');
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'category.name'];
    }

    protected function getWithRelations(): array
    {
        return ['category'];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        $query->when($this->categorySlug, fn (Builder $builder) => $builder->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $this->categorySlug)));

        $query->when($this->stockStatus === 'above', fn (Builder $builder) => $builder->whereColumn('quantity', '>', 'min_quantity'));
        $query->when($this->stockStatus === 'below', fn (Builder $builder) => $builder->whereColumn('quantity', '<', 'min_quantity'));
    }

    public function getProductsProperty()
    {
        return $this->items;
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.products.product-management');
    }

    protected function authorizeManageProducts(): void
    {
        abort_unless(auth()->user()?->can('manage_products'), 403);
    }
}
