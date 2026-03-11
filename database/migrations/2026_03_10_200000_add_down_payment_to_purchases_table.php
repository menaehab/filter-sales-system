<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('down_payment', 10, 2)->default(0)->after('payment_type');
            $table->date('next_installment_date')->nullable()->after('installment_months');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['down_payment', 'next_installment_date']);
        });
    }
};
