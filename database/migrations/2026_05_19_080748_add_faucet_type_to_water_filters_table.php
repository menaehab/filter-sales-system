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
            $table->string('faucet_type')->nullable()->after('is_installed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_filters', function (Blueprint $table) {
            $table->dropColumn('faucet_type');
        });
    }
};
