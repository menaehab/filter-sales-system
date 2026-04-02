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
        Schema::create('water_filter_candle_changes', function (Blueprint $table) {
            $table->id();
            $table->string('candle_key', 30);
            $table->string('candle_name');
            $table->timestamp('replaced_at');
            $table->foreignId('water_filter_id')->constrained('water_filters')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenances')->nullOnDelete();
            $table->timestamps();

            $table->index(['water_filter_id', 'replaced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_filter_candle_changes');
    }
};
