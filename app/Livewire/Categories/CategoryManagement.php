<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'categories_management'])]
class CategoryManagement extends Component
{
    use WithPagination;

    public $form = [
        'name' => '',
    ];

    public $editId = null;
    public $deleteId = null;

    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    protected function rules()
    {
        return [
            'form.name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editId)],
        ];
    }

    protected function getValidationAttributes()
    {
        return [
            'form.name' => __('keywords.name'),
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

    public function resetForm()
    {
        $this->form = [
            'name' => '',
        ];
    }

    public function create()
    {
        $this->editId = null;

        $this->validate();

        Category::create($this->form);

        $this->resetForm();

        $this->dispatch('close-modal-create-category');

        $this->resetPage();
    }

    public function openEdit($id)
    {
        $category = Category::findOrFail($id);

        $this->editId = $category->id;

        $this->form['name'] = $category->name;

        $this->dispatch('open-modal-edit-category');
    }

    public function updateCategory()
    {
        $this->validate();

        Category::findOrFail($this->editId)->update($this->form);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-category');

        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->deleteId = $id;

        $this->dispatch('open-modal-delete-category');
    }

    public function delete()
    {
        Category::find($this->deleteId)?->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-category');

        $this->resetPage();
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.categories.category-management');
    }
}
