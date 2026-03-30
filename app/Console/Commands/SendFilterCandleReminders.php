<?php

namespace App\Console\Commands;

use App\Enums\WaterQualityTypeEnum;
use App\Models\User;
use App\Models\WaterFilter;
use App\Notifications\FilterCandleNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class SendFilterCandleReminders extends Command
{
    protected $signature = 'filters:candle-remind';

    protected $description = 'Send reminders for filter candles that need replacement';

    public function handle()
    {
        $filters = WaterFilter::with(['customer', 'readings'])
            ->whereNotNull('installed_at')
            ->get();

        if ($filters->isEmpty()) {
            $this->info('No filters with installation date found.');

            return;
        }

        $admins = User::all();

        if ($admins->isEmpty()) {
            $this->warn('No users found to notify.');

            return;
        }

        $notificationCount = 0;
        $now = now();
        $warningDays = 7;

        foreach ($filters as $filter) {
            $candlesToNotify = $this->getCandlesNeedingNotification($filter, $now, $warningDays);

            foreach ($candlesToNotify as $candle) {
                foreach ($admins as $admin) {
                    $admin->notify(new FilterCandleNotification(
                        $filter,
                        $candle['name'],
                        $candle['due_date']
                    ));
                }
                $notificationCount++;

                // Log activity for each candle notification
                activity()
                    ->withProperties([
                        'filter_id' => $filter->id,
                        'filter_model' => $filter->filter_model,
                        'customer_name' => $filter->customer?->name,
                        'candle_name' => $candle['name'],
                        'due_date' => $candle['due_date'],
                        'notified_users_count' => $admins->count(),
                    ])
                    ->log(__('keywords.activity_send_filter_candle_reminder'));
            }
        }

        $this->info("Sent {$notificationCount} candle reminder notification(s).");
    }

    protected function getCandlesNeedingNotification(WaterFilter $filter, CarbonInterface $now, int $warningDays): array
    {
        $candles = [];

        // Candle 1: Based on pre-installation water quality
        $candle1Due = $this->getCandle1DueDate($filter);
        if ($candle1Due && $this->isDueWithinWarningPeriod($candle1Due, $now, $warningDays)) {
            $candles[] = ['name' => __('keywords.candle_1'), 'due_date' => $candle1Due->toDateString()];
        }

        // Candles 2 & 3: Every 5 months
        $candle23Due = $this->getCandleDueDate($filter, 'candle_2_3_replaced_at', 5);
        if ($candle23Due && $this->isDueWithinWarningPeriod($candle23Due, $now, $warningDays)) {
            $candles[] = ['name' => __('keywords.candle_2_3'), 'due_date' => $candle23Due->toDateString()];
        }

        // Candle 4: When TDS >= 100
        if ($this->isCandle4Due($filter)) {
            $candles[] = ['name' => __('keywords.candle_4_high_tds'), 'due_date' => null];
        }

        // Candle 5: 6 months warning, 8 months urgent
        $candle5Due = $this->getCandleDueDate($filter, 'candle_5_replaced_at', 6);
        if ($candle5Due && $this->isDueWithinWarningPeriod($candle5Due, $now, $warningDays)) {
            $candles[] = ['name' => __('keywords.candle_5'), 'due_date' => $candle5Due->toDateString()];
        }

        // Candle 6: 8 months warning, 10 months urgent
        $candle6Due = $this->getCandleDueDate($filter, 'candle_6_replaced_at', 8);
        if ($candle6Due && $this->isDueWithinWarningPeriod($candle6Due, $now, $warningDays)) {
            $candles[] = ['name' => __('keywords.candle_6'), 'due_date' => $candle6Due->toDateString()];
        }

        // Candle 7: 10 months warning, 12 months urgent
        $candle7Due = $this->getCandleDueDate($filter, 'candle_7_replaced_at', 10);
        if ($candle7Due && $this->isDueWithinWarningPeriod($candle7Due, $now, $warningDays)) {
            $candles[] = ['name' => __('keywords.candle_7'), 'due_date' => $candle7Due->toDateString()];
        }

        return $candles;
    }

    protected function getCandle1DueDate(WaterFilter $filter): ?Carbon
    {
        if (! $filter->installed_at) {
            return null;
        }

        $preReading = $filter->readings()
            ->where('before_installment', true)
            ->oldest()
            ->first();

        $intervalMonths = match ($preReading?->water_quality) {
            WaterQualityTypeEnum::GOOD->value => 3,
            WaterQualityTypeEnum::FAIR->value => 2,
            WaterQualityTypeEnum::POOR->value => 1,
            default => 3,
        };

        $baseDate = $filter->candle_1_replaced_at ?? $filter->installed_at;

        return Carbon::parse($baseDate)->addMonths($intervalMonths);
    }

    protected function getCandleDueDate(WaterFilter $filter, string $replacedAtField, int $months): ?Carbon
    {
        if (! $filter->installed_at) {
            return null;
        }

        $baseDate = $filter->{$replacedAtField} ?? $filter->installed_at;

        return Carbon::parse($baseDate)->addMonths($months);
    }

    protected function isCandle4Due(WaterFilter $filter): bool
    {
        $latestReading = $filter->readings()
            ->where('before_installment', false)
            ->latest()
            ->first();

        return $latestReading && $latestReading->tds >= 100;
    }

    protected function isDueWithinWarningPeriod(Carbon $dueDate, CarbonInterface $now, int $warningDays): bool
    {
        return $dueDate->lte($now->copy()->addDays($warningDays));
    }
}
