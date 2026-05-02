<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\WaterFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncInstallmentStartFromFilter extends Command
{
    protected $signature = 'sales:sync-installment-start {--dry-run}';

    protected $description = 'Sync installment_start_date for existing installment sales from the customer\'s filter installed_at';

    public function handle(): void
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Searching installment sales with placeholder or missing installment_start_date...');

        $sales = Sale::query()
            ->where('payment_type', 'installment')
            ->where(function ($q) {
                $q->whereNull('installment_start_date')
                    ->orWhereRaw('DATE(installment_start_date) = DATE(created_at)');
            })
            ->with('customer')
            ->get();

        if ($sales->isEmpty()) {
            $this->info('No matching installment sales found.');
            return;
        }

        $updated = 0;

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
                // no filter installed for this customer; skip
                continue;
            }

            $installedAt = $filter->installed_at instanceof Carbon ? $filter->installed_at->format('Y-m-d') : (string) $filter->installed_at;

            if ($dryRun) {
                $this->line("[DRY] Sale #{$sale->number} (id: {$sale->id}) -> would set installment_start_date = {$installedAt} (filter id: {$filter->id})");
                continue;
            }

            $sale->installment_start_date = $installedAt;
            $sale->save();
            $updated++;
            $this->line("Updated sale #{$sale->number} (id: {$sale->id}) to {$installedAt}");
        }

        $this->info(($dryRun ? 'Dry run complete.' : "Updated {$updated} sale(s)."));
    }
}
