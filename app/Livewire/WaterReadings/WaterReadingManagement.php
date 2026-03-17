<?php

namespace App\Livewire\WaterReadings;

use App\Enums\WaterQualityTypeEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\WaterReading;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WaterReadingManagement extends Component
{
    use WithSearchAndPagination, HasForm, HasCrudModals, HasCrudQuery, HasValidationAttributes;

    public $customerSlug = '';
    public $customerSearch = '';
    public $waterQuality = '';
    public $customers;
    public $waterQualityOptions;

    public function mount()
    {
        $this->resetForm();
        $this->customers = Customer::orderBy('name')->get();
        $this->waterQualityOptions = WaterQualityTypeEnum::cases();

        if ($this->customerSlug) {
            $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
        }
    }

    public function getWaterReadingsProperty()
    {
        return $this->items;
    }

    protected function getModelClass(): string
    {
        return WaterReading::class;
    }

    protected function rules()
    {
        return [
            'form.technician_name' => ['required', 'string', 'max:255'],
            'form.tds' => ['required', 'numeric', 'min:0'],
            'form.water_quality' => ['required', 'in:' . implode(',', WaterQualityTypeEnum::values())],
            'form.customer_id' => ['required', 'exists:customers,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.technician_name' => __('keywords.technician_name'),
            'form.tds' => __('keywords.tds'),
            'form.water_quality' => __('keywords.water_quality'),
            'form.customer_id' => __('keywords.customer'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'technician_name' => '',
            'tds' => null,
            'water_quality' => null,
            'customer_id' => null,
        ];
    }

    protected function additionalQueryString(): array
    {
        return [
            'customerSlug' => ['as' => 'customer', 'except' => ''],
            'waterQuality' => ['as' => 'water_quality', 'except' => ''],
        ];
    }

    public function updatingCustomerSlug()
    {
        $this->resetPage();

        $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
    }

    public function updatingWaterQuality()
    {
        $this->resetPage();
    }

    protected function applyAdditionalFilters($query): void
    {
        if ($this->customerSlug) {
            $query->whereHas('customer', fn ($q) => $q->where('slug', $this->customerSlug));
        }

        if ($this->waterQuality) {
            $query->where('water_quality', $this->waterQuality);
        }
    }

    public function create()
    {
        $this->authorizeManageWaterReadings();

        $this->validate();

        WaterReading::create($this->form);

        $this->resetForm();
        $this->dispatch('close-modal-create-water-reading');
        $this->resetPage();
    }

    public function openEdit($id)
    {
        $this->authorizeManageWaterReadings();

        $waterReading = WaterReading::findOrFail($id);

        $this->editId = $waterReading->id;

        $this->form = [
            'technician_name' => $waterReading->technician_name,
            'tds' => $waterReading->tds,
            'water_quality' => $waterReading->water_quality,
            'customer_id' => $waterReading->customer_id,
        ];

        $this->dispatch('open-modal-edit-water-reading');
    }

    public function updateWaterReading()
    {
        $this->authorizeManageWaterReadings();

        $this->validate();

        WaterReading::findOrFail($this->editId)->update($this->form);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-water-reading');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->authorizeManageWaterReadings();

        $this->openDeleteModal($id, 'open-modal-delete-water-reading');
    }

    public function delete()
    {
        $this->authorizeManageWaterReadings();

        WaterReading::find($this->deleteId)?->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-water-reading');
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['technician_name', 'customer.name', 'customer.phone'];
    }

    protected function getWithRelations(): array
    {
        return ['customer'];
    }

    public function render()
    {
        return view('livewire.water-readings.water-reading-management');
    }

    public function authorizeManageWaterReadings()
    {
        return auth()->user()->can('manage_water_readings');
    }
}
