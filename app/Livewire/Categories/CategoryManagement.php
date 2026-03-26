<?php

namespace App\Livewire\Categories;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Http\Requests\Categories\CreateCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'categories_management'])]
class CategoryManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public function mount(): void
    {
        $this->resetForm();
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

    public function create(CreateCategoryAction $action): void
    {
        $this->editId = null;

        $request = new CreateCategoryRequest();
        // Map form.* rules to match $this->form array structure
        $rules = collect($request->rules())->mapWithKeys(fn($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn($attr, $key) => ["form.{$key}" => $attr])->toArray();

        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();

        $this->dispatch('close-modal-create-category');

        $this->resetPage();
    }

    public function openEdit($id): void
    {
        $category = Category::findOrFail($id);

        $this->editId = $category->id;

        $this->form['name'] = $category->name;

        $this->dispatch('open-modal-edit-category');
    }

    public function updateCategory(UpdateCategoryAction $action): void
    {
        $request = new UpdateCategoryRequest();
        $request->merge(['id' => $this->editId]);
        // Map form.* rules to match $this->form array structure
        $rules = collect($request->rules())->mapWithKeys(fn($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn($attr, $key) => ["form.{$key}" => $attr])->toArray();

        $validated = $this->validate($rules, $request->messages(), $attributes);

        $category = Category::findOrFail($this->editId);
        $action->execute($category, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-category');

        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->openDeleteModal($id, 'open-modal-delete-category');
    }

    public function delete(DeleteCategoryAction $action): void
    {
        $category = Category::find($this->deleteId);
        if ($category) {
            $action->execute($category);
        }

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-category');

        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.categories.category-management');
    }
}
