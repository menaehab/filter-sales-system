<?php

namespace App\Livewire\Filters;

use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\ServiceVisit;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ServiceVisitManagement extends Component
{
    use WithSearchAndPagination;

    public string $completionStatus = '';
    public array $selectedVisits = [];

    protected function additionalQueryString(): array
    {
        return [
            'completionStatus' => ['as' => 'status', 'except' => ''],
        ];
    }

    public function updatingCompletionStatus(): void
    {
        $this->resetPage();
    }

    public function markCompleted(int $visitId): void
    {
        $this->authorizeManageVisits();

        $visit = ServiceVisit::query()->findOrFail($visitId);

        if (! $visit->is_completed) {
            $visit->update(['is_completed' => true]);
        }
    }

    public function toggleSelectAll(): void
    {
        if (count($this->selectedVisits) === $this->visits->count()) {
            $this->selectedVisits = [];
        } else {
            $this->selectedVisits = $this->visits->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function clearSelection(): void
    {
        $this->selectedVisits = [];
    }

    public function getSelectedVisitsForPrint()
    {
        if (empty($this->selectedVisits)) {
            return collect([]);
        }

        return ServiceVisit::query()
            ->with(['waterFilter.customer.phones'])
            ->whereIn('id', array_map('intval', $this->selectedVisits))
            ->latest('created_at')
            ->get();
    }

    public function getVisitsProperty()
    {
        return ServiceVisit::query()
            ->with(['waterFilter.customer.phones'])
            ->completionStatus($this->completionStatus)
            ->when(filled($this->search), function (Builder $query) {
                $search = trim($this->search);

                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('user_name', 'like', "%{$search}%")
                        ->orWhere('maintenance_type', 'like', "%{$search}%")
                        ->orWhere('technician_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('waterFilter', function (Builder $waterFilterQuery) use ($search) {
                            $waterFilterQuery->where('filter_model', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%")
                                ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                                    $customerQuery->where('name', 'like', "%{$search}%")
                                        ->orWhere('code', 'like', "%{$search}%")
                                        ->orWhereHas('phones', fn (Builder $phoneQuery) => $phoneQuery->where('number', 'like', "%{$search}%"));
                                });
                        });
                });
            })
            ->latest('created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.filters.service-visit-management', [
            'visits' => $this->visits,
            'selectedCount' => count($this->selectedVisits),
            'totalCount' => $this->visits->count(),
        ]);
    }

    protected function authorizeManageVisits(): void
    {
        abort_unless(auth()->user()?->can('manage_service_visits'), 403);
    }
}
