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
        Schema::create('water_filters', function (Blueprint $table) {
            $table->id();
            $table->string('filter_model');
            $table->string('slug')->unique();
            $table->string('address');
            $table->date('installed_at')->nullable();
            $table->date('candle_1_replaced_at')->nullable();
            $table->date('candle_2_3_replaced_at')->nullable();
            $table->date('candle_4_replaced_at')->nullable();
            $table->date('candle_5_replaced_at')->nullable();
            $table->date('candle_6_replaced_at')->nullable();
            $table->date('candle_7_replaced_at')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_filters');
    }
};
