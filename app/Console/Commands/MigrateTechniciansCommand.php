<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateTechniciansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-technicians';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate string technician_names to the technicians table and set technician_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting technician migration...');

        // Get all unique technician names from all three tables
        $waterReadingsNames = \App\Models\WaterReading::whereNotNull('technician_name')->where('technician_name', '!=', '')->distinct()->pluck('technician_name')->toArray();
        $serviceVisitsNames = \App\Models\ServiceVisit::whereNotNull('technician_name')->where('technician_name', '!=', '')->distinct()->pluck('technician_name')->toArray();
        $maintenancesNames = \App\Models\Maintenance::whereNotNull('technician_name')->where('technician_name', '!=', '')->distinct()->pluck('technician_name')->toArray();

        // Merge and get unique names
        $allNames = array_unique(array_merge($waterReadingsNames, $serviceVisitsNames, $maintenancesNames));
        $allNames = array_filter($allNames, fn($name) => !empty(trim($name)));

        $this->info('Found ' . count($allNames) . ' unique technician names.');

        $bar = $this->output->createProgressBar(count($allNames));
        $bar->start();

        foreach ($allNames as $name) {
            $name = trim($name);
            
            // Create or get technician
            $technician = \App\Models\Technician::firstOrCreate(
                ['name' => $name]
            );

            // Update water readings
            \App\Models\WaterReading::where('technician_name', $name)->update(['technician_id' => $technician->id]);
            
            // Update service visits
            \App\Models\ServiceVisit::where('technician_name', $name)->update(['technician_id' => $technician->id]);
            
            // Update maintenances
            \App\Models\Maintenance::where('technician_name', $name)->update(['technician_id' => $technician->id]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully!');
    }
}
