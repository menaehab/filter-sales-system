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
        Schema::table('sales', function (Blueprint $table) {
            $table->date('installment_start_date')->nullable()->after('installment_months');
        });

        // Set default value for existing records
        \Illuminate\Support\Facades\DB::table('sales')->update([
            'installment_start_date' => \Illuminate\Support\Facades\DB::raw('DATE(created_at)')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('installment_start_date');
        });
    }
};
