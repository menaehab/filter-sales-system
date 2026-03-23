<?php

namespace App\Livewire\Filters;

use App\Enums\WaterQualityTypeEnum;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterView extends Component
{
    use WithSearchAndPagination;

    public WaterFilter $filter;

    public array $readingForm = [
        'technician_name' => '',
        'tds' => '',
        'water_quality' => '',
        'before_installment' => false,
    ];

    public ?string $selectedCandle = null;

    public function mount(WaterFilter $filter): void
    {
        $this->filter = $filter->load('customer');
    }

    public function getReadingsProperty()
    {
        return $this->filter->readings()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getCandlesProperty(): array
    {
        $status = $this->filter->candle_status;

        return [
            [
                'key' => 'candle_1',
                'name' => __('keywords.candle_1'),
                'status' => $status['candle_1'],
                'next_date' => $this->filter->candle_1_next_date,
                'replaced_at' => $this->filter->candle_1_replaced_at,
                'interval' => $this->filter->candle_1_interval_months.' '.__('keywords.months'),
            ],
            [
                'key' => 'candle_2_3',
                'name' => __('keywords.candle_2_3'),
                'status' => $status['candle_2_3'],
                'next_date' => $this->filter->candle_2_3_next_date,
                'replaced_at' => $this->filter->candle_2_3_replaced_at,
                'interval' => '5 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_4',
                'name' => __('keywords.candle_4'),
                'status' => $status['candle_4'],
                'next_date' => null,
                'replaced_at' => $this->filter->candle_4_replaced_at,
                'interval' => 'TDS >= 80',
            ],
            [
                'key' => 'candle_5',
                'name' => __('keywords.candle_5'),
                'status' => $status['candle_5'],
                'next_date' => $this->filter->candle_5_next_date,
                'replaced_at' => $this->filter->candle_5_replaced_at,
                'interval' => '6-8 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_6',
                'name' => __('keywords.candle_6'),
                'status' => $status['candle_6'],
                'next_date' => $this->filter->candle_6_next_date,
                'replaced_at' => $this->filter->candle_6_replaced_at,
                'interval' => '8-10 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_7',
                'name' => __('keywords.candle_7'),
                'status' => $status['candle_7'],
                'next_date' => $this->filter->candle_7_next_date,
                'replaced_at' => $this->filter->candle_7_replaced_at,
                'interval' => '10-12 '.__('keywords.months'),
            ],
        ];
    }

    public function openAddReading(): void
    {
        $this->resetReadingForm();
        $this->dispatch('open-modal-add-reading');
    }

    public function createReading(): void
    {
        $this->authorize('manage_water_filters');

        $this->validate([
            'readingForm.technician_name' => 'required|string|max:255',
            'readingForm.tds' => 'required|numeric|min:0',
            'readingForm.water_quality' => 'required|in:'.implode(',', WaterQualityTypeEnum::values()),
            'readingForm.before_installment' => 'boolean',
        ], [], [
            'readingForm.technician_name' => __('keywords.technician_name'),
            'readingForm.tds' => __('keywords.tds'),
            'readingForm.water_quality' => __('keywords.water_quality'),
            'readingForm.before_installment' => __('keywords.before_installment'),
        ]);

        // Check if trying to add before_installment reading when one already exists
        if ($this->readingForm['before_installment']) {
            $existingPreReading = $this->filter->readings()->where('before_installment', true)->exists();
            if ($existingPreReading) {
                $this->addError('readingForm.before_installment', __('keywords.before_installment_already_exists'));

                return;
            }
        }

        WaterReading::create([
            'technician_name' => $this->readingForm['technician_name'],
            'tds' => $this->readingForm['tds'],
            'water_quality' => $this->readingForm['water_quality'],
            'before_installment' => $this->readingForm['before_installment'],
            'water_filter_id' => $this->filter->id,
        ]);

        $this->filter->refresh();
        $this->resetReadingForm();
        $this->dispatch('close-modal-add-reading');
        $this->resetPage();
    }

    public function openMarkCandle(string $candleKey): void
    {
        $this->selectedCandle = $candleKey;
        $this->dispatch('open-modal-mark-candle');
    }

    public function markCandleReplaced(): void
    {
        $this->authorize('manage_water_filters');

        if ($this->selectedCandle) {
            $this->filter->markCandleReplaced($this->selectedCandle);
            $this->filter->refresh();
        }

        $this->selectedCandle = null;
        $this->dispatch('close-modal-mark-candle');
    }

    protected function resetReadingForm(): void
    {
        $this->readingForm = [
            'technician_name' => '',
            'tds' => '',
            'water_quality' => '',
            'before_installment' => false,
        ];
    }

    public function render()
    {
        return view('livewire.filters.filter-view', [
            'readings' => $this->readings,
            'candles' => $this->candles,
            'waterQualityOptions' => WaterQualityTypeEnum::cases(),
        ]);
    }
}
