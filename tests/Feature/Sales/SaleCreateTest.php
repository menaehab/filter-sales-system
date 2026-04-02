<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Place;
use App\Models\Product;
use App\Models\Sale;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates a cash sale and syncs inventory and payment records', function () {
    $customer = Customer::factory()->create(['name' => 'Acme Customer']);
    $product = Product::factory()->create([
        'name' => 'Primary Filter',
        'cost_price' => 50,
        'quantity' => 5,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('dealer_name', 'Walk-in Dealer')
        ->set('payment_type', 'cash')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '50',
            'sell_price' => '80',
            'available_quantity' => 5,
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales.create'));

    $sale = Sale::with(['items', 'paymentAllocations'])->sole();

    $this->assertEquals('cash', $sale->payment_type);
    $this->assertEquals('Walk-in Dealer', $sale->dealer_name);
    $this->assertEquals(160.0, (float) $sale->total_price);
    $this->assertEquals(160.0, (float) $sale->down_payment);
    $this->assertNull($sale->installment_months);

    $product->refresh();

    $this->assertEquals(3.0, (float) $product->quantity);

    $this->assertDatabaseHas('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'cost_price' => '50.00',
        'sell_price' => '80.00',
        'quantity' => '2.00',
    ]);

    $this->assertDatabaseHas('product_movements', [
        'movable_type' => Sale::class,
        'movable_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => '-2.00',
    ]);

    $payment = CustomerPayment::sole();

    $this->assertDatabaseHas('customer_payments', [
        'id' => $payment->id,
        'customer_id' => $customer->id,
        'amount' => '160.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'customer_payment_id' => $payment->id,
        'sale_id' => $sale->id,
        'amount' => '160.00',
    ]);
});

it('creates an installment sale with down payment and next installment date', function () {
    Carbon::setTestNow('2026-03-11 10:00:00');

    $customer = Customer::factory()->create(['name' => 'Installment Customer']);
    $product = Product::factory()->create([
        'cost_price' => 40,
        'quantity' => 8,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'installment')
        ->set('down_payment', '30')
        ->set('installment_months', '3')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '40',
            'sell_price' => '90',
            'available_quantity' => 8,
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales.create'));

    $sale = Sale::sole();

    $this->assertEquals('installment', $sale->payment_type);
    $this->assertEquals(480.0, (float) $sale->total_price);
    $this->assertEquals(30.0, (float) $sale->down_payment);
    $this->assertEquals(150.0, (float) $sale->installment_amount);
    $this->assertEquals(3, $sale->installment_months);
    $this->assertEquals('2026-04-11', $sale->next_installment_date?->toDateString());

    $this->assertDatabaseHas('customer_payments', [
        'customer_id' => $customer->id,
        'amount' => '30.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'sale_id' => $sale->id,
        'amount' => '30.00',
    ]);

    Carbon::setTestNow();
});

it('allows setting created_at on sale create when user has manage_created_at permission', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'cost_price' => 40,
        'quantity' => 5,
    ]);

    Livewire::test('sales.sale-create')
        ->assertSeeHtml('name="created_at"')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('created_at', '2026-01-10T09:30')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '40',
            'sell_price' => '70',
            'available_quantity' => 5,
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::sole();
    $payment = CustomerPayment::sole();

    expect($sale->created_at->toDateTimeString())->toBe('2026-01-10 09:30:00');
    expect($payment->created_at->toDateTimeString())->toBe('2026-01-10 09:30:00');
});

it('ignores created_at on sale create when user lacks manage_created_at permission', function () {
    $limitedUser = User::factory()->create();
    $limitedUser->givePermissionTo('manage_sales');
    $this->actingAs($limitedUser);

    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'cost_price' => 30,
        'quantity' => 5,
    ]);

    Livewire::test('sales.sale-create')
        ->assertDontSeeHtml('name="created_at"')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('created_at', '2020-01-01T00:00')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '30',
            'sell_price' => '55',
            'available_quantity' => 5,
            'quantity' => '1',
        ]])
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::sole();
    $payment = CustomerPayment::sole();

    expect($sale->created_at->greaterThanOrEqualTo(now()->subMinute()))->toBeTrue();
    expect($sale->created_at->toDateTimeString())->not->toBe('2020-01-01 00:00:00');
    expect($payment->created_at->toDateTimeString())->not->toBe('2020-01-01 00:00:00');
});

it('validates sale fields before saving', function () {
    Livewire::test('sales.sale-create')
        ->set('payment_type', 'installment')
        ->set('down_payment', '')
        ->set('installment_months', '')
        ->set('cart', [[
            'product_id' => '',
            'product_name' => '',
            'category_name' => '',
            'cost_price' => '',
            'sell_price' => '',
            'available_quantity' => 0,
            'quantity' => '',
        ]])
        ->call('save')
        ->assertHasErrors([
            'customer_id' => 'required',
            'down_payment' => 'required_if',
            'installment_months' => 'required_if',
            'cart.0.product_id' => 'required',
            'cart.0.sell_price' => 'required',
            'cart.0.quantity' => 'required',
        ]);
});

it('creates a place inline while creating a customer', function () {
    Livewire::test('sales.sale-create')
        ->set('newPlace.name', 'Inline Place Cairo')
        ->call('createPlaceInline')
        ->assertHasNoErrors();

    $place = Place::query()->where('name', 'Inline Place Cairo')->first();

    expect($place)->not->toBeNull();

    Livewire::test('sales.sale-create')
        ->set('newPlace.name', 'Inline Place Giza')
        ->call('createPlaceInline')
        ->assertSet('newCustomer.place_id', (string) Place::query()->where('name', 'Inline Place Giza')->value('id'));
});

    it('requires selecting a customer before opening filter modal', function () {
        Livewire::test('sales.sale-create')
        ->call('openCreateFilterModal')
        ->assertHasErrors(['water_filter_id']);
    });

it('creates a filter inline during sale creation', function () {
    $customer = Customer::factory()->create(['name' => 'Inline Filter Customer']);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('createNewFilter', true)
        ->set('newFilter.filter_model', 'Inline Filter Model')
        ->set('newFilter.address', 'Inline Filter Address')
        ->call('createFilterInline')
        ->assertHasNoErrors()
        ->assertSet('createNewFilter', false);

    $filter = WaterFilter::query()
        ->where('customer_id', $customer->id)
        ->where('filter_model', 'Inline Filter Model')
        ->where('address', 'Inline Filter Address')
        ->first();

    expect($filter)->not->toBeNull();
});

it('caps add to cart quantity at available stock', function () {
    $product = Product::factory()->create([
        'name' => 'Limited Product',
        'cost_price' => 40,
        'quantity' => 2,
    ]);

    Livewire::test('sales.sale-create')
        ->call('addToCart', $product->id)
        ->call('addToCart', $product->id)
        ->call('addToCart', $product->id)
        ->assertSet('cart.0.quantity', '2');
});

it('blocks sale creation when cart quantity exceeds product stock', function () {
    $customer = Customer::factory()->create(['name' => 'Stock Check Customer']);
    $product = Product::factory()->create([
        'name' => 'Low Stock Product',
        'cost_price' => 20,
        'quantity' => 1,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '20',
            'sell_price' => '30',
            'available_quantity' => 999,
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasErrors(['cart']);

    $this->assertDatabaseCount('sales', 0);

    $product->refresh();
    $this->assertEquals(1.0, (float) $product->quantity);
});

it('applies available customer credit to a new sale before cash payment', function () {
    $customer = Customer::factory()->create(['name' => 'Credit Customer']);
    $product = Product::factory()->create([
        'cost_price' => 25,
        'quantity' => 20,
    ]);

    $oldSale = Sale::create([
        'dealer_name' => 'Old Dealer',
        'user_name' => auth()->user()->name,
        'total_price' => 300,
        'payment_type' => 'cash',
        'user_id' => auth()->id(),
        'customer_id' => $customer->id,
    ]);

    $oldSalePayment = CustomerPayment::create([
        'customer_id' => $customer->id,
        'amount' => 450,
        'payment_method' => 'cash',
        'user_id' => auth()->id(),
    ]);

    CustomerPaymentAllocation::create([
        'customer_payment_id' => $oldSalePayment->id,
        'sale_id' => $oldSale->id,
        'amount' => 300,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '25',
            'sell_price' => '50',
            'available_quantity' => 20,
            'quantity' => '10',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales.create'));

    $sale = Sale::whereKeyNot($oldSale->id)->latest('id')->first();
    $customer->refresh();

    $this->assertEquals(500.0, (float) $sale->total_price);
    $this->assertEquals(500.0, (float) $sale->paid_amount);
    $this->assertEquals(0.0, (float) $sale->remaining_amount);
    $this->assertEquals(0.0, (float) $customer->available_credit);
    $this->assertEquals(0.0, (float) $customer->balance);

    $this->assertDatabaseHas('customer_payments', [
        'customer_id' => $customer->id,
        'amount' => '350.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('customer_payments', [
        'customer_id' => $customer->id,
        'amount' => '150.00',
        'payment_method' => 'customer_credit',
    ]);

    $this->assertDatabaseHas('customer_payment_allocations', [
        'sale_id' => $sale->id,
        'amount' => '150.00',
    ]);
});

it('creates a sale and stores water reading when enabled', function () {
    $customer = Customer::factory()->create(['name' => 'Water Reading Customer']);
    $filter = WaterFilter::create([
        'filter_model' => 'Customer Filter',
        'address' => 'Customer Address',
        'customer_id' => $customer->id,
    ]);

    $product = Product::factory()->create([
        'name' => 'Water Filter',
        'cost_price' => 70,
        'quantity' => 6,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('includeWaterReading', true)
        ->set('water_filter_id', $filter->id)
        ->set('waterReading.technician_name', 'Technician A')
        ->set('waterReading.tds', '145')
        ->set('waterReading.water_quality', 'fair')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '70',
            'sell_price' => '100',
            'available_quantity' => 6,
            'quantity' => '2',
        ]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales.create'));

    $sale = Sale::sole();
    $this->assertEquals(200.0, (float) $sale->total_price);

    $this->assertDatabaseHas('water_readings', [
        'technician_name' => 'Technician A',
        'tds' => '145.00',
        'water_quality' => 'fair',
        'water_filter_id' => $filter->id,
    ]);

    $this->assertEquals(1, WaterReading::query()->count());
});

it('creates before and after installation readings when requested', function () {
    $customer = Customer::factory()->create(['name' => 'Dual Reading Customer']);
    $filter = WaterFilter::create([
        'filter_model' => 'Dual Reading Filter',
        'address' => 'Dual Reading Address',
        'customer_id' => $customer->id,
    ]);

    $product = Product::factory()->create([
        'name' => 'Dual Reading Product',
        'cost_price' => 60,
        'quantity' => 4,
    ]);

    Livewire::test('sales.sale-create')
        ->set('customer_id', $customer->id)
        ->set('payment_type', 'cash')
        ->set('includeWaterReading', true)
        ->set('water_filter_id', $filter->id)
        ->set('waterReading.technician_name', 'Technician Before')
        ->set('waterReading.tds', '130')
        ->set('waterReading.water_quality', 'fair')
        ->set('waterReading.before_installment', true)
        ->set('includeAfterInstallationReading', true)
        ->set('afterWaterReading.technician_name', 'Technician After')
        ->set('afterWaterReading.tds', '95')
        ->set('afterWaterReading.water_quality', 'good')
        ->set('cart', [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category_name' => 'Filters',
            'cost_price' => '60',
            'sell_price' => '90',
            'available_quantity' => 4,
            'quantity' => '1',
        ]])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('water_readings', [
        'water_filter_id' => $filter->id,
        'technician_name' => 'Technician Before',
        'tds' => '130.00',
        'water_quality' => 'fair',
        'before_installment' => 1,
    ]);

    $this->assertDatabaseHas('water_readings', [
        'water_filter_id' => $filter->id,
        'technician_name' => 'Technician After',
        'tds' => '95.00',
        'water_quality' => 'good',
        'before_installment' => 0,
    ]);

    $this->assertEquals(2, WaterReading::query()->where('water_filter_id', $filter->id)->count());
});
