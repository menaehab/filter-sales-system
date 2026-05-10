<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sale;
use App\Models\CustomerPaymentAllocation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For each sale, find the first payment allocation and mark it as is_down_payment = true
        $sales = Sale::all();

        foreach ($sales as $sale) {
            $firstAllocation = $sale->paymentAllocations()
                ->whereHas('customerPayment', function ($query) {
                    $query->where('payment_method', '!=', 'customer_credit');
                })
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstAllocation) {
                $firstAllocation->update(['is_down_payment' => true]);
                $firstAllocation->customerPayment->update(['is_down_payment' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse this without losing manual changes, 
        // but we can set all to false if needed.
    }
};
