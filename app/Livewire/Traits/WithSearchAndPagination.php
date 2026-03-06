<?php

namespace App\Livewire\Traits;

use Livewire\WithPagination;

trait WithSearchAndPagination
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected function queryString()
    {
        return array_merge([
            'search' => ['except' => ''],
            'perPage' => ['except' => 10],
            'page' => ['except' => 1],
        ], $this->additionalQueryString());
    }

    protected function additionalQueryString(): array
    {
        return [];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }
}
