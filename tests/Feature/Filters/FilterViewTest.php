<?php

use App\Enums\WaterQualityTypeEnum;
use App\Models\Customer;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('displays the filter view', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->assertSee('Model A')
        ->assertSee('Address A');
});

it('creates a water reading', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('readingForm.technician_name', 'Ahmed Technician')
        ->set('readingForm.tds', 150)
        ->set('readingForm.water_quality', WaterQualityTypeEnum::GOOD->value)
        ->call('createReading')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-add-reading');

    $this->assertDatabaseHas('water_readings', [
        'technician_name' => 'Ahmed Technician',
        'tds' => '150.00',
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'water_filter_id' => $filter->id,
    ]);
});

it('validates required water reading fields', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);
    
    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('readingForm.technician_name', '')
        ->set('readingForm.tds', null)
        ->set('readingForm.water_quality', null)
        ->call('createReading')
        ->assertHasErrors([
            'readingForm.technician_name' => 'required',
            'readingForm.tds' => 'required',
            'readingForm.water_quality' => 'required',
        ]);
});
