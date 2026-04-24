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
        Schema::table('water_filters', function (Blueprint $table) {
            $table->unique('customer_id', 'water_filters_customer_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_filters', function (Blueprint $table) {
            $table->dropUnique('water_filters_customer_id_unique');
        });
    }
};
