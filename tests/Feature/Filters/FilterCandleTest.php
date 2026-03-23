<?php

use App\Enums\WaterQualityTypeEnum;
use App\Models\Customer;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Carbon\Carbon;

beforeEach(function () {
    actAsAdmin($this);
});

it('calculates candle 1 interval based on water quality (GOOD)', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => true,
    ]);

    $filter->refresh();

    expect($filter->candle_1_interval_months)->toBe(3);
});

it('calculates candle 1 interval based on water quality (FAIR)', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 150,
        'water_quality' => WaterQualityTypeEnum::FAIR->value,
        'before_installment' => true,
    ]);

    $filter->refresh();

    expect($filter->candle_1_interval_months)->toBe(2);
});

it('calculates candle 1 interval based on water quality (POOR)', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 200,
        'water_quality' => WaterQualityTypeEnum::POOR->value,
        'before_installment' => true,
    ]);

    $filter->refresh();

    expect($filter->candle_1_interval_months)->toBe(1);
});

it('calculates candle 1 next date correctly', function () {
    $customer = Customer::factory()->create();
    $installedDate = Carbon::parse('2026-01-01');

    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => $installedDate,
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => true,
    ]);

    $filter->refresh();

    $expectedDate = $installedDate->copy()->addMonths(3);
    expect($filter->candle_1_next_date->format('Y-m-d'))->toBe($expectedDate->format('Y-m-d'));
});

it('checks candle 4 needs replacement when TDS >= 80', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 85,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => false,
    ]);

    $filter->refresh();

    expect($filter->candle_4_needs_replacement)->toBeTrue();
});

it('checks candle 4 does not need replacement when TDS < 80', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 70,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => false,
    ]);

    $filter->refresh();

    expect($filter->candle_4_needs_replacement)->toBeFalse();
});

it('marks candle as replaced', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    expect($filter->candle_1_replaced_at)->toBeNull();

    $filter->markCandleReplaced('candle_1');
    $filter->refresh();

    expect($filter->candle_1_replaced_at)->not->toBeNull();
});

it('returns correct candle status colors', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now()->subMonths(5), // 5 months ago
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Test Technician',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'before_installment' => true,
    ]);

    $filter->refresh();
    $status = $filter->candle_status;

    expect($status)->toBeArray();
    expect($status)->toHaveKeys(['candle_1', 'candle_2_3', 'candle_4', 'candle_5', 'candle_6', 'candle_7']);
    expect($status['candle_1'])->toBeIn(['success', 'warning', 'danger']);
});

it('calculates all candle next dates', function () {
    $customer = Customer::factory()->create();
    $installedDate = Carbon::parse('2026-01-01');

    $filter = WaterFilter::create([
        'filter_model' => 'Test Model',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => $installedDate,
    ]);

    $filter->refresh();

    // Test candle 2_3 (5 months)
    expect($filter->candle_2_3_next_date->format('Y-m-d'))
        ->toBe($installedDate->copy()->addMonths(5)->format('Y-m-d'));

    // Test candle 5 (6 months)
    expect($filter->candle_5_next_date->format('Y-m-d'))
        ->toBe($installedDate->copy()->addMonths(6)->format('Y-m-d'));

    // Test candle 6 (8 months)
    expect($filter->candle_6_next_date->format('Y-m-d'))
        ->toBe($installedDate->copy()->addMonths(8)->format('Y-m-d'));

    // Test candle 7 (10 months)
    expect($filter->candle_7_next_date->format('Y-m-d'))
        ->toBe($installedDate->copy()->addMonths(10)->format('Y-m-d'));
});
