<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Place;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use App\Notifications\CustomerInstallmentDueNotification;
use App\Notifications\FilterCandleNotification;
use App\Notifications\LowStockNotification;
use App\Notifications\SupplierInstallmentDueNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');

            return;
        }

        $this->command->info('Creating comprehensive test notifications...');
        $this->command->newLine();

        // Create basic demo entities for manual testing.
        $this->seedDemoEntities();

        // 1. Customer Installment Notifications
        $this->seedCustomerInstallments($users);

        // 2. Low Stock Notifications
        $this->seedLowStock($users);

        // 3. Supplier Installment Notifications
        $this->seedSupplierInstallments($users);

        // 4. Filter Candle Notifications (all supported types)
        $this->seedFilterCandles($users);

        $this->command->newLine();
        $this->command->info('Notification seeding completed successfully!');
        $this->command->info("Total users notified: {$users->count()}");
    }

    protected function seedDemoEntities(): void
    {
        $this->command->info('[0/4] Creating demo customer, supplier, product, sale, purchase, and filter...');

        $user = User::query()->first();
        $place = Place::create([
            'name' => 'مكان تجريبي',
        ]);

        $customer = Customer::firstOrCreate(
            ['name' => 'عميل تجريبي'],
            [
                'phone' => '01000000001',
                'national_number' => '12345678901234',
                'address' => 'عنوان تجريبي',
                'place_id' => $place->id,
            ]
        );

        $supplier = Supplier::firstOrCreate(
            ['name' => 'مورد تجريبي'],
            ['phone' => '01000000002']
        );

        $category = Category::firstOrCreate(['name' => 'فئة تجريبية']);

        $product = Product::firstOrCreate(
            ['name' => 'منتج تجريبي'],
            [
                'description' => 'منتج للتجربة عبر NotificationSeeder',
                'cost_price' => 100,
                'quantity' => 2,
                'min_quantity' => 5,
                'category_id' => $category->id,
            ]
        );

        // Keep the demo product in low-stock state for notification testing.
        $product->update([
            'quantity' => 2,
            'min_quantity' => 5,
            'category_id' => $product->category_id ?: $category->id,
        ]);

        $sale = Sale::updateOrCreate(
            [
                'dealer_name' => 'فاتورة تجريبية للإشعارات',
                'customer_id' => $customer->id,
            ],
            [
                'user_name' => $user?->name ?? 'System',
                'total_price' => 1500,
                'payment_type' => 'installment',
                'installment_amount' => 500,
                'installment_months' => 3,
                'user_id' => $user?->id,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]
        );

        $purchase = Purchase::updateOrCreate(
            [
                'supplier_name' => $supplier->name,
                'supplier_id' => $supplier->id,
            ],
            [
                'user_name' => $user?->name ?? 'System',
                'total_price' => 1200,
                'payment_type' => 'installment',
                'installment_amount' => 400,
                'installment_months' => 3,
                'user_id' => $user?->id,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
            ]
        );

        $filter = WaterFilter::updateOrCreate(
            [
                'filter_model' => 'فلتر تجريبي للإشعارات',
                'customer_id' => $customer->id,
            ],
            [
                'address' => 'عنوان الفلتر التجريبي',
                'installed_at' => now()->subMonths(3)->toDateString(),
            ]
        );

        WaterReading::updateOrCreate(
            [
                'water_filter_id' => $filter->id,
                'before_installment' => true,
            ],
            [
                'technician_name' => 'فني تجريبي',
                'tds' => 100,
                'water_quality' => 'good',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]
        );

        $this->command->info("Demo customer ready: {$customer->name}");
        $this->command->info("Demo supplier ready: {$supplier->name}");
        $this->command->info("Demo product ready: {$product->name}");
        $this->command->info("Demo installment sale ready: {$sale->number}");
        $this->command->info("Demo installment purchase ready: {$purchase->number}");
        $this->command->info("Demo installed filter ready: {$filter->filter_model}");
        $this->command->newLine();
    }

    protected function seedCustomerInstallments($users): void
    {
        $this->command->info('[1/4] Creating Customer Installment notifications...');

        $customerSales = Sale::whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->with('customer')
            ->take(3)
            ->get();

        if ($customerSales->isEmpty()) {
            $this->command->warn('No customer installment sales found');

            return;
        }

        foreach ($users as $user) {
            foreach ($customerSales as $sale) {
                $user->notify(new CustomerInstallmentDueNotification($sale));
            }
        }

        $this->command->info("Created {$customerSales->count()} customer installment notifications");
    }

    protected function seedLowStock($users): void
    {
        $this->command->info('[2/4] Creating Low Stock notifications...');

        $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_quantity')
            ->where('min_quantity', '>', 0)
            ->take(3)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->command->warn('No low stock products found');

            return;
        }

        foreach ($users as $user) {
            foreach ($lowStockProducts as $product) {
                $user->notify(new LowStockNotification($product));
            }
        }

        $this->command->info("Created {$lowStockProducts->count()} low stock notifications");
    }

    protected function seedSupplierInstallments($users): void
    {
        $this->command->info('[3/4] Creating Supplier Installment notifications...');

        $supplierPurchases = Purchase::whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->take(3)
            ->get();

        if ($supplierPurchases->isEmpty()) {
            $this->command->warn('No supplier installment purchases found');

            return;
        }

        foreach ($users as $user) {
            foreach ($supplierPurchases as $purchase) {
                $user->notify(new SupplierInstallmentDueNotification($purchase));
            }
        }

        $this->command->info("Created {$supplierPurchases->count()} supplier installment notifications");
    }

    protected function seedFilterCandles($users): void
    {
        $this->command->info('[4/4] Creating Filter Candle notifications (ALL SUPPORTED TYPES)...');

        $filters = WaterFilter::whereNotNull('installed_at')
            ->with('customer')
            ->take(5)
            ->get();

        if ($filters->isEmpty()) {
            $this->command->warn('No installed filters found');

            return;
        }

        // All candle types with Arabic names
        $candleTypes = [
            ['name' => 'شمعة 1 (حسب جودة المياه)', 'key' => 'candle_1'],
            ['name' => 'شمعة 2 و 3', 'key' => 'candle_2_3'],
            ['name' => 'شمعة 4 (TDS)', 'key' => 'candle_4'],
            ['name' => 'شمعة 5', 'key' => 'candle_5'],
            ['name' => 'شمعة 6', 'key' => 'candle_6'],
            ['name' => 'شمعة 7', 'key' => 'candle_7'],
        ];

        $totalNotifications = 0;

        foreach ($filters as $filter) {
            // Test all supported candle notification types for comprehensive testing
            foreach ($candleTypes as $candle) {
                $dueDate = now()->addDays(rand(-5, 14))->toDateString();

                foreach ($users as $user) {
                    $user->notify(new FilterCandleNotification(
                        $filter,
                        $candle['name'],
                        $dueDate
                    ));
                }

                $totalNotifications++;
            }
        }

        $this->command->info("Created {$totalNotifications} filter candle notifications");
        $this->command->info("Tested all supported candle types × {$filters->count()} filters = {$totalNotifications} total");
        $this->command->newLine();
        $this->command->info('  Candle types tested:');
        foreach ($candleTypes as $candle) {
            $this->command->info("    • {$candle['name']}");
        }
    }
}
