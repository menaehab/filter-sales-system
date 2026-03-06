<?php

namespace App\Livewire\Traits;

trait HasCrudModals
{
    public $editId = null;
    public $deleteId = null;

    public function openEditModal($id, string $modalEvent)
    {
        $this->editId = $id;
        $this->dispatch($modalEvent);
    }

    public function openDeleteModal($id, string $modalEvent)
    {
        $this->deleteId = $id;
        $this->dispatch($modalEvent);
    }

    public function resetEditDelete()
    {
        $this->editId = null;
        $this->deleteId = null;
    }
}
