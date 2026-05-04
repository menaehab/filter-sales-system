<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\WaterFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AdjustInstallmentStartDateByMonth extends Command
{
    protected $signature = 'sales:adjust-installment-start {--dry-run}';

    protected $description = 'Adjust installment_start_date to be one month after filter installation date if they currently match';

    public function handle(): void
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Searching for installment sales where start date matches installation date...');

        $sales = Sale::query()
            ->where('payment_type', 'installment')
            ->whereNotNull('installment_start_date')
            ->with('customer')
            ->get();

        if ($sales->isEmpty()) {
            $this->info('No installment sales found.');
            return;
        }

        $updated = 0;
        $matched = 0;

        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;
            if (! $customerId) {
                continue;
            }

            $filter = WaterFilter::where('customer_id', $customerId)
                ->whereNotNull('installed_at')
                ->orderBy('installed_at', 'asc')
                ->first();

            if (! $filter) {
                continue;
            }

            // Compare dates only (Y-m-d)
            $startAt = $sale->installment_start_date instanceof Carbon ? $sale->installment_start_date->format('Y-m-d') : Carbon::parse($sale->installment_start_date)->format('Y-m-d');
            $installedAt = $filter->installed_at instanceof Carbon ? $filter->installed_at->format('Y-m-d') : Carbon::parse($filter->installed_at)->format('Y-m-d');

            if ($startAt === $installedAt) {
                $matched++;
                $newDate = Carbon::parse($filter->installed_at)->addMonth()->format('Y-m-d');

                if ($dryRun) {
                    $this->line("[DRY] Sale #{$sale->number} (id: {$sale->id}): Current {$startAt} matches installation. Would set to {$newDate}");
                    continue;
                }

                $sale->installment_start_date = $newDate;
                $sale->save();
                $updated++;
                $this->line("Updated sale #{$sale->number} (id: {$sale->id}) to {$newDate}");
            }
        }

        $this->info("Total matched: {$matched}");
        $this->info(($dryRun ? 'Dry run complete.' : "Successfully updated {$updated} sale(s)."));
    }
}
