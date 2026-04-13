<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('water_filters', function (Blueprint $table) {
            $table->boolean('is_installed')->default(false)->after('installed_at');
        });

        DB::table('water_filters')
            ->whereNotNull('installed_at')
            ->update(['is_installed' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_filters', function (Blueprint $table) {
            $table->dropColumn('is_installed');
        });
    }
};
