<?php

namespace App\Livewire\Traits;

trait HasForm
{
    public $form = [];

    public function resetForm()
    {
        $this->form = $this->getDefaultForm();
    }

    abstract protected function getDefaultForm(): array;
}
