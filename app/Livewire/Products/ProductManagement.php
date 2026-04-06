<?php

namespace App\Livewire\Products;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Products\CreateProductAction;
use App\Actions\Products\DeleteProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public array $newCategory = [
        'name' => '',
    ];

    public $categorySlug = '';

    public $stockStatus = '';

    public $forMaintenance = '';

    public function mount(): void
    {
        $this->resetForm();
        $this->newCategory = ['name' => ''];
    }

    protected function getModelClass(): string
    {
        return Product::class;
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
            'for_maintenance' => false,
        ];
    }

    protected function additionalQueryString(): array
    {
        return [
            'categorySlug' => ['as' => 'category', 'except' => ''],
            'stockStatus' => ['as' => 'stock', 'except' => ''],
            'forMaintenance' => ['as' => 'maintenance', 'except' => ''],
        ];
    }

    public function updatingCategorySlug(): void
    {
        $this->resetPage();
    }

    public function updatingStockStatus(): void
    {
        $this->resetPage();
    }

    public function create(CreateProductAction $action): void
    {
        $this->authorizeManageProducts();

        $request = new \App\Http\Requests\Products\CreateProductRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();
        $this->dispatch('close-modal-create-product');
        $this->resetPage();
    }

    public function openCreateCategoryModal(): void
    {
        $this->newCategory = ['name' => ''];
        $this->dispatch('open-modal-create-category-inline');
    }

    public function createCategoryInline(CreateCategoryAction $action): void
    {
        $request = new \App\Http\Requests\Categories\CreateCategoryRequest;
        $validated = $this->validate(
            collect($request->rules())->mapWithKeys(fn ($rules, $key) => ["newCategory.{$key}" => $rules])->toArray(),
            $request->messages(),
            collect($request->attributes())->mapWithKeys(fn ($label, $key) => ["newCategory.{$key}" => $label])->toArray()
        );

        $category = $action->execute($validated['newCategory']);

        $this->newCategory = ['name' => ''];
        $this->form['category_id'] = $category->id;
        $this->dispatch('close-modal-create-category-inline');

        if ($this->editId) {
            $this->dispatch('open-modal-edit-product');

            return;
        }

        $this->dispatch('open-modal-create-product');
    }

    public function openEdit($id): void
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

    public function updateProduct(UpdateProductAction $action): void
    {
        $this->authorizeManageProducts();

        $request = new \App\Http\Requests\Products\UpdateProductRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $product = Product::findOrFail($this->editId);
        $action->execute($product, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-product');
        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->authorizeManageProducts();

        $this->openDeleteModal($id, 'open-modal-delete-product');
    }

    public function delete(DeleteProductAction $action): void
    {
        $this->authorizeManageProducts();

        $product = Product::find($this->deleteId);
        if ($product) {
            $action->execute($product);
        }

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
        $query->when($this->forMaintenance === 'yes', fn (Builder $builder) => $builder->where('for_maintenance', true));
        $query->when($this->forMaintenance === 'no', fn (Builder $builder) => $builder->where('for_maintenance', false));
    }

    #[Computed]
    public function products()
    {
        return $this->items;
    }

    #[Computed]
    public function categories(): Collection
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
