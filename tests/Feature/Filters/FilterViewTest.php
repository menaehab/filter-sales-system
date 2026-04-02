<?php

use App\Enums\WaterQualityTypeEnum;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\WaterFilter;
use App\Models\WaterFilterCandleChange;
use Carbon\Carbon;
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

it('saves maintenance with multiple changed candles and installed products', function () {
    $customer = Customer::factory()->create();
    $user = auth()->user();

    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    $maintenanceProduct = Product::factory()->create([
        'name' => 'Maintenance Product',
        'for_maintenance' => true,
    ]);

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'payment_type' => 'cash',
        'total_price' => 500,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $maintenanceProduct->id,
        'quantity' => 2,
        'sell_price' => 250,
        'cost_price' => 120,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('maintenanceForm.selected_candles', ['candle_1', 'candle_4'])
        ->set('maintenanceForm.technician_name', 'Mahmoud')
        ->set('maintenanceForm.replaced_at', '2026-03-12T10:15')
        ->set('maintenanceForm.cost', '150')
        ->set('maintenanceForm.description', 'Full maintenance visit')
        ->set('maintenanceForm.items.'.$maintenanceProduct->id, 2)
        ->call('saveMaintenance')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-mark-candle');

    $maintenance = Maintenance::query()->first();

    expect($maintenance)->not->toBeNull();
    expect((float) $maintenance->cost)->toBe(150.0);
    expect($maintenance->technician_name)->toBe('Mahmoud');

    $this->assertDatabaseHas('maintenance_items', [
        'maintenance_id' => $maintenance->id,
        'sale_item_id' => $saleItem->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('water_filter_candle_changes', [
        'maintenance_id' => $maintenance->id,
        'water_filter_id' => $filter->id,
        'candle_key' => 'candle_1',
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('water_filter_candle_changes', [
        'maintenance_id' => $maintenance->id,
        'water_filter_id' => $filter->id,
        'candle_key' => 'candle_4',
        'user_id' => $user->id,
    ]);

    $filter->refresh();

    expect($filter->candle_1_replaced_at)->not->toBeNull();
    expect($filter->candle_4_replaced_at)->not->toBeNull();

    $this->assertDatabaseHas('water_filter_candle_changes', [
        'maintenance_id' => $maintenance->id,
        'candle_key' => 'candle_1',
        'replaced_at' => '2026-03-12 10:15:00',
    ]);
});

it('validates maintenance product quantity against remaining purchased quantity', function () {
    $customer = Customer::factory()->create();
    $user = auth()->user();

    $filter = WaterFilter::create([
        'filter_model' => 'Model A',
        'address' => 'Address A',
        'customer_id' => $customer->id,
    ]);

    $maintenanceProduct = Product::factory()->create([
        'name' => 'Limited Product',
        'for_maintenance' => true,
    ]);

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'payment_type' => 'cash',
        'total_price' => 300,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $maintenanceProduct->id,
        'quantity' => 2,
        'sell_price' => 150,
        'cost_price' => 80,
    ]);

    $existingMaintenance = Maintenance::create([
        'cost' => 50,
        'technician_name' => 'Previous Tech',
        'description' => 'Previous maintenance',
        'user_id' => $user->id,
        'water_filter_id' => $filter->id,
    ]);

    MaintenanceItem::create([
        'maintenance_id' => $existingMaintenance->id,
        'sale_item_id' => $saleItem->id,
        'quantity' => 1,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('maintenanceForm.selected_candles', ['candle_5'])
        ->set('maintenanceForm.technician_name', 'New Tech')
        ->set('maintenanceForm.replaced_at', '2026-03-12T10:15')
        ->set('maintenanceForm.cost', '80')
        ->set('maintenanceForm.items.'.$maintenanceProduct->id, 2)
        ->call('saveMaintenance')
        ->assertHasErrors([
            'maintenanceForm.items.'.$maintenanceProduct->id,
        ]);

    expect(Maintenance::query()->count())->toBe(1);
});

it('uses current time for replaced_at when user lacks manage_created_at permission', function () {
    $limitedUser = User::factory()->create();
    $limitedUser->givePermissionTo('manage_water_filters');
    $this->actingAs($limitedUser);

    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model B',
        'address' => 'Address B',
        'customer_id' => $customer->id,
    ]);

    $maintenanceProduct = Product::factory()->create([
        'name' => 'Maintenance Product 2',
        'for_maintenance' => true,
    ]);

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $limitedUser->id,
        'user_name' => $limitedUser->name,
        'payment_type' => 'cash',
        'total_price' => 200,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $maintenanceProduct->id,
        'quantity' => 1,
        'sell_price' => 200,
        'cost_price' => 80,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('maintenanceForm.selected_candles', ['candle_6'])
        ->set('maintenanceForm.technician_name', 'No Permission Tech')
        ->set('maintenanceForm.replaced_at', '2026-01-01T08:00')
        ->set('maintenanceForm.cost', '90')
        ->set('maintenanceForm.items.'.$maintenanceProduct->id, 1)
        ->call('saveMaintenance')
        ->assertHasNoErrors();

    $change = WaterFilterCandleChange::query()->latest('id')->first();

    expect($change)->not->toBeNull();
    expect($change->replaced_at->greaterThanOrEqualTo(now()->subMinute()))->toBeTrue();
    expect($change->replaced_at->toDateTimeString())->not->toBe('2026-01-01 08:00:00');
});

it('allows editing replaced_at when user has manage_created_at permission', function () {
    $customer = Customer::factory()->create();
    $user = auth()->user();

    $filter = WaterFilter::create([
        'filter_model' => 'Model C',
        'address' => 'Address C',
        'customer_id' => $customer->id,
    ]);

    $maintenanceProduct = Product::factory()->create([
        'name' => 'Maintenance Product 3',
        'for_maintenance' => true,
    ]);

    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'payment_type' => 'cash',
        'total_price' => 220,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $maintenanceProduct->id,
        'quantity' => 1,
        'sell_price' => 220,
        'cost_price' => 100,
    ]);

    Livewire::test('filters.filter-view', ['filter' => $filter])
        ->set('maintenanceForm.selected_candles', ['candle_7'])
        ->set('maintenanceForm.technician_name', 'Permission Tech')
        ->set('maintenanceForm.replaced_at', '2026-04-15T14:45')
        ->set('maintenanceForm.cost', '120')
        ->set('maintenanceForm.items.'.$maintenanceProduct->id, 1)
        ->call('saveMaintenance')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('water_filter_candle_changes', [
        'water_filter_id' => $filter->id,
        'candle_key' => 'candle_7',
        'replaced_at' => '2026-04-15 14:45:00',
    ]);
});
