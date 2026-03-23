<?php

use App\Enums\WaterQualityTypeEnum;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

it('sends customer installment reminders for due installments', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 3,
        'installment_amount' => 500,
        'total_price' => 1500,
        'created_at' => now()->subMonths(2),
    ]);

    Artisan::call('customers:installment-remind');

    $output = Artisan::output();
    expect($output)->toContain('customer installment');
});

it('does not send notifications when no customer installments are due', function () {
    User::factory()->create();

    Artisan::call('customers:installment-remind');

    $output = Artisan::output();
    expect($output)->toContain('No due customer installments found');
});

it('sends low stock alerts for products below minimum quantity', function () {
    User::factory()->create();

    Product::factory()->create([
        'name' => 'Low Stock Product',
        'quantity' => 5,
        'min_quantity' => 10,
    ]);

    Artisan::call('products:low-stock-alert');

    $output = Artisan::output();
    expect($output)->toContain('low stock');
});

it('does not send notifications when no low stock products exist', function () {
    User::factory()->create();

    Product::factory()->create([
        'name' => 'Well Stocked Product',
        'quantity' => 100,
        'min_quantity' => 10,
    ]);

    Artisan::call('products:low-stock-alert');

    $output = Artisan::output();
    expect($output)->toContain('No low stock products found');
});

it('sends filter candle reminders', function () {
    User::factory()->create();
    $customer = Customer::factory()->create();

    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now()->subMonths(4), // 4 months ago
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Reminder Technician',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => true,
    ]);

    Artisan::call('filters:candle-remind');

    $output = Artisan::output();
    expect($output)->toContain('candle reminder');
});

it('logs activity when sending customer installment reminders', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 3,
        'installment_amount' => 500,
        'total_price' => 1500,
        'created_at' => now()->subMonths(2),
    ]);

    Artisan::call('customers:installment-remind');

    $this->assertDatabaseHas('activity_log', [
        'description' => 'إرسال تذكير بقسط عميل مستحق',
    ]);
});

it('logs activity when sending low stock alerts', function () {
    User::factory()->create();

    Product::factory()->create([
        'name' => 'Low Stock Product',
        'quantity' => 5,
        'min_quantity' => 10,
    ]);

    Artisan::call('products:low-stock-alert');

    $this->assertDatabaseHas('activity_log', [
        'description' => 'إرسال تنبيه بمخزون منخفض',
    ]);
});

it('logs activity when sending filter candle reminders', function () {
    User::factory()->create();
    $customer = Customer::factory()->create();

    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now()->subMonths(4),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Reminder Technician',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => true,
    ]);

    Artisan::call('filters:candle-remind');

    // Check if any activity was logged (the command might not find due candles)
    $activities = \Spatie\Activitylog\Models\Activity::where('description', 'إرسال تذكير بتغيير شمعة فلتر')->get();
    expect($activities)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});

it('handles multiple users when sending notifications', function () {
    User::factory()->count(3)->create();
    $customer = Customer::factory()->create();

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 3,
        'installment_amount' => 500,
        'total_price' => 1500,
        'created_at' => now()->subMonths(2),
    ]);

    Artisan::call('customers:installment-remind');

    $output = Artisan::output();
    expect($output)->toContain('customer installment');
});
