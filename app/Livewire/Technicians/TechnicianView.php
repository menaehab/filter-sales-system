<?php

namespace App\Livewire\Technicians;

use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Technician;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TechnicianView extends Component
{
    use WithSearchAndPagination;

    public Technician $technician;

    public string $activeTab = 'maintenances';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(Technician $technician): void
    {
        $this->technician = $technician;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getMaintenancesProperty()
    {
        return $this->technician->maintenances()
            ->with(['filter.customer', 'candleChanges', 'items.saleItem.product'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getServiceVisitsProperty()
    {
        return $this->technician->serviceVisits()
            ->with(['waterFilter.customer'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getWaterReadingsProperty()
    {
        return $this->technician->waterReadings()
            ->with(['waterFilter.customer'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getInstalledFiltersProperty()
    {
        return $this->technician->installedWaterFilters()
            ->with(['customer'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('installed_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('installed_at', '<=', $this->dateTo))
            ->orderBy('installed_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.technicians.technician-view', [
            'maintenances' => $this->maintenances,
            'serviceVisits' => $this->serviceVisits,
            'waterReadings' => $this->waterReadings,
            'installedFilters' => $this->installedFilters,
        ]);
    }
}
