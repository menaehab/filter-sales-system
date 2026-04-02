<?php

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Maintenance;
use App\Models\Sale;
use App\Models\User;
use App\Models\WaterFilter;
use App\Services\StatisticsService;
use Carbon\Carbon;

it('treats maintenance cost as revenue in net profit', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    Sale::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'payment_type' => 'cash',
        'total_price' => 300,
    ]);

    Expense::factory()->create([
        'amount' => 100,
        'user_id' => $user->id,
    ]);

    Maintenance::create([
        'cost' => 40,
        'technician_name' => 'Tech Main',
        'description' => 'Maintenance visit',
        'user_id' => $user->id,
        'water_filter_id' => $filter->id,
    ]);

    $service = app(StatisticsService::class);

    expect($service->getTotalExpenses())->toBe(100.0);
    expect($service->getNetProfit())->toBe(240.0);
});

it('adds maintenance revenue to profit over time', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    try {
        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00'));

        Sale::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'payment_type' => 'cash',
            'total_price' => 200,
        ]);

        Maintenance::create([
            'cost' => 30,
            'technician_name' => 'Tech A',
            'description' => 'First maintenance',
            'user_id' => $user->id,
            'water_filter_id' => $filter->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-11 10:00:00'));

        Sale::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'payment_type' => 'cash',
            'total_price' => 100,
        ]);

        Maintenance::create([
            'cost' => 40,
            'technician_name' => 'Tech B',
            'description' => 'Second maintenance',
            'user_id' => $user->id,
            'water_filter_id' => $filter->id,
        ]);
    } finally {
        Carbon::setTestNow();
    }

    $service = app(StatisticsService::class);

    $profitByDate = collect($service->getProfitOverTime(groupBy: 'day'))
        ->keyBy('date');

    expect($profitByDate->has('2026-03-10'))->toBeTrue();
    expect($profitByDate->has('2026-03-11'))->toBeTrue();

    expect((float) $profitByDate->get('2026-03-10')['profit'])->toBe(230.0);
    expect((float) $profitByDate->get('2026-03-11')['profit'])->toBe(140.0);
});
