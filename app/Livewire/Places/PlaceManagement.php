<?php

namespace App\Livewire\Places;

use App\Actions\Places\CreatePlaceAction;
use App\Actions\Places\DeletePlaceAction;
use App\Actions\Places\UpdatePlaceAction;
use App\Http\Requests\Place\CreatePlaceRequest;
use App\Http\Requests\Place\UpdatePlaceRequest;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Place;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'places_management'])]
class PlaceManagement extends Component
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
            'user_ids' => [],
        ];
    }

    protected function getModelClass(): string
    {
        return Place::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'users.name'];
    }

    protected function getWithRelations(): array
    {
        return ['users'];
    }

    #[Computed]
    public function userOptions(): array
    {
        return User::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function create(CreatePlaceAction $action): void
    {
        $this->editId = null;

        $request = new CreatePlaceRequest;
        // Map form.* rules to match $this->form array structure
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();

        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();

        $this->dispatch('close-modal-create-place');

        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $place = Place::with('users:id')->findOrFail($id);

        $this->form = [
            'name' => $place->name,
            'user_ids' => $place->users->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ];

        $this->openEditModal($id, 'open-modal-edit-place');
    }

    public function updatePlace(UpdatePlaceAction $action): void
    {
        $request = new UpdatePlaceRequest;
        $request->merge(['id' => $this->editId]);
        // Map form.* rules to match $this->form array structure
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();

        $validated = $this->validate($rules, $request->messages(), $attributes);

        $place = Place::findOrFail($this->editId);
        $action->execute($place, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-place');

        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->openDeleteModal($id, 'open-modal-delete-place');
    }

    public function delete(DeletePlaceAction $action): void
    {
        $place = Place::find($this->deleteId);
        if ($place) {
            $action->execute($place);
        }

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-place');

        $this->resetPage();
    }

    #[Computed]
    public function places()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.places.place-management');
    }
}
