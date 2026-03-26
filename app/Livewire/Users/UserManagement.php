<?php

namespace App\Livewire\Users;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.app', ['title' => 'users_management'])]
class UserManagement extends Component
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
            'email' => '',
            'phone' => '',
            'password' => '',
            'password_confirmation' => '',
            'permissions' => [],
        ];
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'phone'];
    }

    #[Computed]
    public function permissionOptions(): array
    {
        return Permission::pluck('name', 'name')
            ->mapWithKeys(fn ($name) => [$name => __('keywords.'.$name)])
            ->toArray();
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return $this->items;
    }

    private function mapRules(array $rules): array
    {
        return collect($rules)->mapWithKeys(function ($rule, $key) {
            $rule = is_array($rule) ? $rule : explode('|', $rule);
            $rule = array_map(function ($r) {
                if (is_string($r) && str_starts_with($r, 'required_without:')) {
                    return 'required_without:form.' . substr($r, 17);
                }
                return $r;
            }, $rule);
            return ["form.{$key}" => $rule];
        })->toArray();
    }

    public function create(CreateUserAction $action): void
    {
        $this->editId = null;

        $request = new \App\Http\Requests\Users\CreateUserRequest;
        $rules = $this->mapRules($request->rules());
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();
        $this->dispatch('close-modal-create-user');
        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editId = $id;
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => '',
            'password_confirmation' => '',
            'permissions' => $user->getPermissionNames()->toArray(),
        ];

        $this->dispatch('open-modal-edit-user');
    }

    public function updateUser(UpdateUserAction $action): void
    {
        // Add the user ID to the form data for unique validation
        $this->form['id'] = $this->editId;

        $request = new \App\Http\Requests\Users\UpdateUserRequest;
        $request->merge($this->form);

        $rules = $this->mapRules($request->rules());
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $user = User::findOrFail($this->editId);
        $action->execute($user, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-user');
        $this->resetPage();
    }

    public function openDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-user');
    }

    public function setDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-user');
    }

    public function delete(DeleteUserAction $action): void
    {
        $user = User::find($this->deleteId);

        if ($user) {
            $action->execute($user);
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-user');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.users.user-management');
    }
}
