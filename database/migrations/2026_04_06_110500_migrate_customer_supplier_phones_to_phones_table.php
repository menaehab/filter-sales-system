<?php

use App\Models\Customer;
use App\Models\Supplier;
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
        if (Schema::hasColumn('customers', 'phone')) {
            Customer::query()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->select(['id', 'phone'])
                ->chunkById(200, function ($customers): void {
                    $now = now();

                    $rows = $customers->map(fn (Customer $customer) => [
                        'number' => $customer->phone,
                        'phoneable_type' => Customer::class,
                        'phoneable_id' => $customer->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    if ($rows !== []) {
                        DB::table('phones')->insert($rows);
                    }
                });

            Schema::table('customers', function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasColumn('suppliers', 'phone')) {
            Supplier::query()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->select(['id', 'phone'])
                ->chunkById(200, function ($suppliers): void {
                    $now = now();

                    $rows = $suppliers->map(fn (Supplier $supplier) => [
                        'number' => $supplier->phone,
                        'phoneable_type' => Supplier::class,
                        'phoneable_id' => $supplier->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    if ($rows !== []) {
                        DB::table('phones')->insert($rows);
                    }
                });

            Schema::table('suppliers', function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('customers', 'phone')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->string('phone')->nullable()->after('slug');
            });

            Customer::query()->with('phones')->chunkById(200, function ($customers): void {
                foreach ($customers as $customer) {
                    $firstPhone = $customer->phones->first()?->number;

                    if (filled($firstPhone)) {
                        DB::table('customers')
                            ->where('id', $customer->id)
                            ->update(['phone' => $firstPhone]);
                    }
                }
            });
        }

        if (! Schema::hasColumn('suppliers', 'phone')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->string('phone')->nullable()->after('slug');
            });

            Supplier::query()->with('phones')->chunkById(200, function ($suppliers): void {
                foreach ($suppliers as $supplier) {
                    $firstPhone = $supplier->phones->first()?->number;

                    if (filled($firstPhone)) {
                        DB::table('suppliers')
                            ->where('id', $supplier->id)
                            ->update(['phone' => $firstPhone]);
                    }
                }
            });
        }
    }
};
