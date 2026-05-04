<?php

namespace App\Livewire\Technicians;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Technician;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TechnicianManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public bool $isEditing = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function getModelClass(): string
    {
        return Technician::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'phone'];
    }

    protected function getDefaultForm(): array
    {
        return [
            'name' => '',
            'phone' => '',
        ];
    }

    public function openCreateModal(string $modalEvent): void
    {
        $this->isEditing = false;
        $this->resetEditDelete();
        $this->resetForm();
        $this->dispatch($modalEvent);
    }

    public function create(): void
    {
        $this->authorizeManageTechnicians();

        $validated = $this->validate([
            'form.name' => ['required', 'string', 'max:255', Rule::unique('technicians', 'name')],
            'form.phone' => ['nullable', 'string', 'max:255'],
        ], [], [
            'form.name' => __('keywords.technician_name'),
            'form.phone' => __('keywords.phone'),
        ]);

        Technician::create($validated['form']);
        
        $this->resetForm();
        $this->dispatch('close-modal-create-technician');
        $this->resetPage();
    }

    public function openEdit($id): void
    {
        $this->authorizeManageTechnicians();
        $this->isEditing = true;

        $technician = Technician::findOrFail($id);

        $this->openEditModal($technician->id, 'open-modal-edit-technician');

        $this->form = [
            'name' => $technician->name,
            'phone' => $technician->phone,
        ];
    }

    public function update(): void
    {
        $this->authorizeManageTechnicians();

        $validated = $this->validate([
            'form.name' => ['required', 'string', 'max:255', Rule::unique('technicians', 'name')->ignore($this->editId)],
            'form.phone' => ['nullable', 'string', 'max:255'],
        ], [], [
            'form.name' => __('keywords.technician_name'),
            'form.phone' => __('keywords.phone'),
        ]);

        $technician = Technician::findOrFail($this->editId);
        $technician->update($validated['form']);
        
        $this->isEditing = false;
        $this->resetForm();
        $this->editId = null;
        $this->dispatch('close-modal-edit-technician');
    }

    public function setDelete($id): void
    {
        $this->authorizeManageTechnicians();

        $this->openDeleteModal($id, 'open-modal-delete-technician');
    }

    public function delete(): void
    {
        $this->authorizeManageTechnicians();

        $technician = Technician::find($this->deleteId);
        if ($technician) {
            $technician->delete();
        }
        
        $this->isEditing = false;
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-technician');
        $this->resetPage();
    }

    #[Computed]
    public function technicians()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.technicians.technician-management');
    }

    protected function authorizeManageTechnicians(): void
    {
        abort_unless(auth()->user()?->can('manage_technicians'), 403);
    }
}
