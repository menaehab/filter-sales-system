<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define mapping: targetId => [sourceIds]
        $mapping = [
            1 => [2, 3],
            4 => [5, 8, 7],
            6 => [9],
        ];

        foreach ($mapping as $targetId => $sourceIds) {
            $targetTech = DB::table('technicians')->find($targetId);
            if (!$targetTech) {
                // If target doesn't exist, we skip or handle as error. 
                // Given the request, we assume 1, 4, 6 exist.
                continue;
            }

            $name = $targetTech->name;

            // Tables that reference technicians
            $tables = ['water_readings', 'service_visits', 'maintenances', 'water_filters'];

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) continue;

                $updateData = ['technician_id' => $targetId];
                
                // If the table has a technician_name string column, update it too
                if (Schema::hasColumn($table, 'technician_name')) {
                    $updateData['technician_name'] = $name;
                }

                DB::table($table)
                    ->whereIn('technician_id', $sourceIds)
                    ->update($updateData);
                
                // Also handle cases where technician_name might be used without ID (if applicable)
                // but usually id is the primary reference.
            }

            // Finally, delete the source technicians
            DB::table('technicians')->whereIn('id', $sourceIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Consolidation is generally non-reversible without backups
    }
};
