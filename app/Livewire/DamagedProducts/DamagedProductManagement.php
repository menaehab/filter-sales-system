<?php

namespace App\Livewire\DamagedProducts;

use App\Actions\DamagedProducts\CreateDamagedProductAction;
use App\Actions\DamagedProducts\DeleteDamagedProductAction;
use App\Actions\DamagedProducts\UpdateDamagedProductAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\DamagedProduct;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'damaged_products_management'])]
class DamagedProductManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public string $productSlug = '';

    public string $productSearch = '';

    public string $formProductSearch = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->resetForm();

        if ($this->productSlug) {
            $product = $this->products->firstWhere('slug', $this->productSlug);
            $this->productSearch = $product?->name ?? '';
        }
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::with('category')->orderBy('name')->get();
    }

    #[Computed]
    public function damagedProducts(): LengthAwarePaginator
    {
        return $this->items;
    }

    protected function getModelClass(): string
    {
        return DamagedProduct::class;
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

    public function updatingProductSlug(): void
    {
        $this->resetPage();
        $product = $this->products->firstWhere('slug', $this->productSlug);
        $this->productSearch = $product?->name ?? '';
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    protected function applyAdditionalFilters($query): void
    {
        if ($this->productSlug) {
            $query->whereHas('product', fn ($q) => $q->where('slug', $this->productSlug));
        }

        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
    }

    public function create(CreateDamagedProductAction $action): void
    {
        $this->authorizeManageDamagedProducts();

        $request = new \App\Http\Requests\DamagedProducts\CreateDamagedProductRequest;
        $request->merge($this->form);
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();
        $this->formProductSearch = '';
        $this->dispatch('close-modal-create-damaged-product');
        $this->resetPage();
        unset($this->products);
    }

    public function openEdit(int $id): void
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

    public function updateDamagedProduct(UpdateDamagedProductAction $action): void
    {
        $this->authorizeManageDamagedProducts();

        $request = new \App\Http\Requests\DamagedProducts\UpdateDamagedProductRequest;
        $request->merge($this->form);
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $damagedProduct = DamagedProduct::findOrFail($this->editId);
        $action->execute($damagedProduct, $validated['form']);

        $this->resetForm();
        $this->formProductSearch = '';
        $this->editId = null;

        $this->dispatch('close-modal-edit-damaged-product');
        $this->resetPage();
        unset($this->products);
    }

    public function setDelete(int $id): void
    {
        $this->authorizeManageDamagedProducts();
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-damaged-product');
    }

    public function delete(DeleteDamagedProductAction $action): void
    {
        $this->authorizeManageDamagedProducts();

        $damagedProduct = DamagedProduct::find($this->deleteId);

        if ($damagedProduct) {
            $action->execute($damagedProduct);
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-damaged-product');
        $this->resetPage();
        unset($this->products);
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

    private function authorizeManageDamagedProducts(): void
    {
        abort_unless(auth()->user()->can('manage_damaged_products'), 403);
    }
}
