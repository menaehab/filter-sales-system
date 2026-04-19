<?php

namespace App\Livewire\Filters;

use App\Models\ServiceVisit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'service_visit_details'])]
class ServiceVisitShow extends Component
{
    public ServiceVisit $serviceVisit;

    public function mount(ServiceVisit $serviceVisit): void
    {
        $this->serviceVisit = $serviceVisit->load(['waterFilter.customer.phones', 'user']);
    }

    public function markCompleted(): void
    {
        abort_unless(auth()->user()?->can('manage_service_visits'), 403);

        if (! $this->serviceVisit->is_completed) {
            $this->serviceVisit->update(['is_completed' => true]);
            $this->serviceVisit->refresh();
        }
    }

    public function render()
    {
        return view('livewire.filters.service-visit-show');
    }
}
