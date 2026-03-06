<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductManagement extends Component
{
    use WithPagination;

    public $form = [
        'name' => '',
        'cost_price' => null,
        'quantity' => null,
        'description' => '',
        'category_id' => null,
    ];

    public $search = '';
    public $perPage = 10;
    public $editId = null;
    public $deleteId = null;
    public $categorySlug = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
        'categorySlug' => ['as' => 'category', 'except' => ''],
    ];

    protected function rules()
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.cost_price' => ['required', 'numeric', 'min:0'],
            'form.quantity' => ['required', 'integer', 'min:0'],
            'form.description' => ['nullable', 'string'],
            'form.category_id' => ['required', 'exists:categories,id'],
        ];
    }

    protected function getValidationAttributes()
    {
        return [
            'form.name' => __('keywords.name'),
            'form.cost_price' => __('keywords.cost_price'),
            'form.quantity' => __('keywords.quantity'),
            'form.description' => __('keywords.description'),
            'form.category_id' => __('keywords.category'),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingCategorySlug()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'cost_price' => null,
            'quantity' => null,
            'description' => '',
            'category_id' => null,
        ];
    }

    public function create()
    {
        $this->validate();

        Product::create($this->form);

        $this->resetForm();
        $this->dispatch('close-modal-create-product');
        $this->resetPage();
    }

    public function openEdit($id)
    {
        $product = Product::findOrFail($id);

        $this->editId = $product->id;

        $this->form = [
            'name' => $product->name,
            'cost_price' => $product->cost_price,
            'quantity' => $product->quantity,
            'description' => $product->description,
            'category_id' => $product->category_id,
        ];

        $this->dispatch('open-modal-edit-product');
    }

    public function updateProduct()
    {
        $this->validate();

        Product::findOrFail($this->editId)->update($this->form);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-product');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-product');
    }

    public function delete()
    {
        Product::find($this->deleteId)?->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-product');
        $this->resetPage();
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->categorySlug, fn($q) => $q->whereHas('category', fn($q) => $q->where('slug', $this->categorySlug)))
            ->latest()
            ->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.products.product-management');
    }
}
