<?php

namespace App\Livewire\Categories;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'categories_management'])]
class CategoryManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, HasValidationAttributes, WithSearchAndPagination;

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [
            'form.name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editId)],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => __('keywords.name'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'name' => '',
        ];
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
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
        $this->openDeleteModal($id, 'open-modal-delete-category');
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
        return $this->items;
    }

    public function render()
    {
        return view('livewire.categories.category-management');
    }
}
