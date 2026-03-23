<?php

use App\Enums\WaterQualityTypeEnum;
use App\Models\Customer;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a water reading', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    Livewire::test('water-readings.water-reading-management')
        ->set('form.technician_name', 'Ahmed Technician')
        ->set('form.tds', 150)
        ->set('form.water_quality', WaterQualityTypeEnum::GOOD->value)
        ->set('form.water_filter_id', $filter->id)
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-create-water-reading');

    $this->assertDatabaseHas('water_readings', [
        'technician_name' => 'Ahmed Technician',
        'tds' => '150.00',
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'water_filter_id' => $filter->id,
    ]);
});

it('validates required water reading fields', function () {
    Livewire::test('water-readings.water-reading-management')
        ->set('form.technician_name', '')
        ->set('form.tds', null)
        ->set('form.water_quality', null)
        ->set('form.water_filter_id', null)
        ->call('create')
        ->assertHasErrors([
            'form.technician_name' => 'required',
            'form.tds' => 'required',
            'form.water_quality' => 'required',
            'form.water_filter_id' => 'required',
        ]);
});

it('validates filter existence when creating a water reading', function () {
    Livewire::test('water-readings.water-reading-management')
        ->set('form.technician_name', 'Samy')
        ->set('form.tds', 110)
        ->set('form.water_quality', WaterQualityTypeEnum::FAIR->value)
        ->set('form.water_filter_id', 99999)
        ->call('create')
        ->assertHasErrors(['form.water_filter_id' => 'exists']);
});

it('updates a water reading', function () {
    $oldCustomer = Customer::factory()->create();
    $newCustomer = Customer::factory()->create();
    $oldFilter = WaterFilter::create([
        'filter_model' => 'Old Filter',
        'address' => 'Old Address',
        'customer_id' => $oldCustomer->id,
    ]);
    $newFilter = WaterFilter::create([
        'filter_model' => 'New Filter',
        'address' => 'New Address',
        'customer_id' => $newCustomer->id,
    ]);

    $reading = WaterReading::create([
        'water_filter_id' => $oldFilter->id,
        'technician_name' => 'Old Tech',
        'tds' => 90,
        'water_quality' => WaterQualityTypeEnum::POOR->value,
    ]);

    Livewire::test('water-readings.water-reading-management')
        ->call('openEdit', $reading->id)
        ->set('form.technician_name', 'Updated Tech')
        ->set('form.tds', 130)
        ->set('form.water_quality', WaterQualityTypeEnum::GOOD->value)
        ->set('form.water_filter_id', $newFilter->id)
        ->call('updateWaterReading')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-edit-water-reading');

    $this->assertDatabaseHas('water_readings', [
        'id' => $reading->id,
        'technician_name' => 'Updated Tech',
        'tds' => '130.00',
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
        'water_filter_id' => $newFilter->id,
    ]);
});

it('deletes a water reading', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Delete Filter',
        'address' => 'Delete Address',
        'customer_id' => $customer->id,
    ]);

    $reading = WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Delete Tech',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::FAIR->value,
    ]);

    Livewire::test('water-readings.water-reading-management')
        ->call('setDelete', $reading->id)
        ->call('delete')
        ->assertDispatched('close-modal-delete-water-reading');

    $this->assertDatabaseMissing('water_readings', [
        'id' => $reading->id,
    ]);
});

it('filters water readings by search term', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Search Filter',
        'address' => 'Search Address',
        'customer_id' => $customer->id,
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Ali Search',
        'tds' => 120,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Mahmoud Other',
        'tds' => 180,
        'water_quality' => WaterQualityTypeEnum::FAIR->value,
    ]);

    $component = Livewire::test('water-readings.water-reading-management')
        ->set('search', 'Ali');

    $readings = $component->get('waterReadings');

    $this->assertSame(1, $readings->total());
    $this->assertSame(['Ali Search'], collect($readings->items())->pluck('technician_name')->all());
});

it('filters water readings by customer slug', function () {
    $firstCustomer = Customer::factory()->create(['name' => 'First Customer']);
    $secondCustomer = Customer::factory()->create(['name' => 'Second Customer']);
    $firstFilter = WaterFilter::create([
        'filter_model' => 'Filter 1',
        'address' => 'Address 1',
        'customer_id' => $firstCustomer->id,
    ]);
    $secondFilter = WaterFilter::create([
        'filter_model' => 'Filter 2',
        'address' => 'Address 2',
        'customer_id' => $secondCustomer->id,
    ]);

    WaterReading::create([
        'water_filter_id' => $firstFilter->id,
        'technician_name' => 'Tech 1',
        'tds' => 100,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
    ]);

    WaterReading::create([
        'water_filter_id' => $secondFilter->id,
        'technician_name' => 'Tech 2',
        'tds' => 140,
        'water_quality' => WaterQualityTypeEnum::FAIR->value,
    ]);

    $component = Livewire::test('water-readings.water-reading-management')
        ->set('customerSlug', $firstCustomer->slug);

    $readings = $component->get('waterReadings');

    $this->assertSame(1, $readings->total());
    $this->assertSame(['Tech 1'], collect($readings->items())->pluck('technician_name')->all());
});

it('filters water readings by water quality', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Quality Filter',
        'address' => 'Quality Address',
        'customer_id' => $customer->id,
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Tech Good',
        'tds' => 80,
        'water_quality' => WaterQualityTypeEnum::GOOD->value,
    ]);

    WaterReading::create([
        'water_filter_id' => $filter->id,
        'technician_name' => 'Tech Poor',
        'tds' => 220,
        'water_quality' => WaterQualityTypeEnum::POOR->value,
    ]);

    $component = Livewire::test('water-readings.water-reading-management')
        ->set('waterQuality', WaterQualityTypeEnum::GOOD->value);

    $readings = $component->get('waterReadings');

    $this->assertSame(1, $readings->total());
    $this->assertSame(['Tech Good'], collect($readings->items())->pluck('technician_name')->all());
});

it('paginates water readings using per page selection', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Paging Filter',
        'address' => 'Paging Address',
        'customer_id' => $customer->id,
    ]);

    foreach (range(1, 15) as $i) {
        WaterReading::create([
            'water_filter_id' => $filter->id,
            'technician_name' => "Tech {$i}",
            'tds' => 100 + $i,
            'water_quality' => WaterQualityTypeEnum::GOOD->value,
        ]);
    }

    $component = Livewire::test('water-readings.water-reading-management')
        ->set('perPage', 10);

    $this->assertCount(10, $component->get('waterReadings'));

    $component->call('setPage', 2);

    $this->assertCount(5, $component->get('waterReadings'));
});

it('resets page when search, per page, customer slug, or quality filter changes', function () {
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Reset Filter',
        'address' => 'Reset Address',
        'customer_id' => $customer->id,
    ]);

    foreach (range(1, 30) as $i) {
        WaterReading::create([
            'water_filter_id' => $filter->id,
            'technician_name' => "Reset Tech {$i}",
            'tds' => 120 + $i,
            'water_quality' => WaterQualityTypeEnum::GOOD->value,
        ]);
    }

    $component = Livewire::test('water-readings.water-reading-management');

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('waterReadings')->currentPage());

    $component->set('search', 'Tech');
    $this->assertSame(1, $component->get('waterReadings')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('waterReadings')->currentPage());

    $component->set('perPage', 25);
    $this->assertSame(1, $component->get('waterReadings')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('waterReadings')->currentPage());

    $component->set('customerSlug', $customer->slug);
    $this->assertSame(1, $component->get('waterReadings')->currentPage());

    $component->call('setPage', 2);
    $this->assertSame(2, $component->get('waterReadings')->currentPage());

    $component->set('waterQuality', WaterQualityTypeEnum::GOOD->value);
    $this->assertSame(1, $component->get('waterReadings')->currentPage());
});
