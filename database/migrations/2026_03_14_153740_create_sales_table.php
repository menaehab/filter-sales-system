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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('dealer_name')->nullable();
            $table->string('user_name');
            $table->decimal('total_price', 15, 2);
            $table->string('payment_type');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->unsignedInteger('installment_months')->nullable();
            $table->boolean('with_vat')->default(false);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
