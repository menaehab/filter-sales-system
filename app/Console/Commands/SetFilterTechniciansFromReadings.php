<?php

namespace App\Console\Commands;

use App\Models\WaterFilter;
use App\Models\WaterReading;
use Illuminate\Console\Command;

class SetFilterTechniciansFromReadings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filters:set-technicians';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set technician_id and technician_name on filters from their pre-installation readings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filters = WaterFilter::with('readings.technician')->get();
        $updatedCount = 0;

        $this->info('Starting to process ' . $filters->count() . ' filters...');

        foreach ($filters as $filter) {
            $preReading = $filter->readings->where('before_installment', true)->sortBy('created_at')->first();

            if ($preReading && $preReading->technician_id) {
                $filter->update([
                    'technician_id' => $preReading->technician_id,
                    'technician_name' => $preReading->technician->name ?? null,
                ]);
                $updatedCount++;
            }
        }

        $this->info('Successfully updated ' . $updatedCount . ' filters.');
    }
}
