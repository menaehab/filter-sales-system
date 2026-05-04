<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('water_readings', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
        });

        Schema::table('service_visits', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });

        Schema::table('service_visits', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });

        Schema::table('water_readings', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });
    }
};
