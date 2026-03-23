<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.app', ['title' => 'users_management'])]
class UserManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, HasValidationAttributes, WithSearchAndPagination;

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editId), 'required_without:form.phone'],
            'form.phone' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/', Rule::unique('users', 'phone')->ignore($this->editId), 'required_without:form.email'],
            'form.password' => $this->editId ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'],
            'form.permissions' => ['array'],
            'form.permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => __('keywords.name'),
            'form.email' => __('keywords.email'),
            'form.phone' => __('keywords.phone'),
            'form.password' => __('keywords.password'),
            'form.password_confirmation' => __('keywords.confirm_password'),
            'form.permissions' => __('keywords.permissions'),
        ];
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

    public function getPermissionOptionsProperty()
    {
        return Permission::pluck('name', 'name')
            ->mapWithKeys(fn ($name) => [$name => __('keywords.'.$name)])
            ->toArray();
    }

    public function create()
    {
        $this->editId = null;

        $this->validate();

        $user = User::create([
            'name' => $this->form['name'],
            'email' => $this->form['email'],
            'phone' => $this->form['phone'] ?: null,
            'password' => Hash::make($this->form['password']),
        ]);

        if (! empty($this->form['permissions'])) {
            $user->syncPermissions($this->form['permissions']);
        }

        $this->resetForm();

        $this->dispatch('close-modal-create-user');

        $this->resetPage();
    }

    public function openEdit($id)
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

    public function updateUser()
    {
        $this->validate();

        $data = [
            'name' => $this->form['name'],
            'email' => $this->form['email'],
            'phone' => $this->form['phone'] ?: null,
        ];

        if ($this->form['password']) {
            $data['password'] = Hash::make($this->form['password']);
        }

        $user = User::findOrFail($this->editId);
        $user->update($data);

        if (! empty($this->form['permissions'])) {
            $user->syncPermissions($this->form['permissions']);
        } else {
            $user->syncPermissions([]);
        }

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-user');

        $this->resetPage();
    }

    public function openDelete($id)
    {
        $this->openDeleteModal($id, 'open-modal-delete-user');
    }

    public function setDelete($id)
    {
        $this->openDeleteModal($id, 'open-modal-delete-user');
    }

    public function delete()
    {
        User::findOrFail($this->deleteId)->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-user');

        $this->resetPage();
    }

    public function getUsersProperty()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.users.user-management');
    }
}
