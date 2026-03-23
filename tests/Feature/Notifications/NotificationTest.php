<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WaterFilter;
use App\Notifications\CustomerInstallmentDueNotification;
use App\Notifications\FilterCandleNotification;
use App\Notifications\LowStockNotification;
use App\Notifications\SupplierInstallmentDueNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    actAsAdmin($this);
});

it('creates customer installment notification with correct data', function () {
    $user = User::first();
    $customer = Customer::factory()->create(['name' => 'Test Customer']);
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 6,
        'installment_amount' => 500,
        'total_price' => 3000,
    ]);

    Notification::fake();

    $user->notify(new CustomerInstallmentDueNotification($sale));

    Notification::assertSentTo($user, CustomerInstallmentDueNotification::class);
});

it('creates low stock notification with correct data', function () {
    $user = User::first();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'quantity' => 5,
        'min_quantity' => 10,
    ]);

    Notification::fake();

    $user->notify(new LowStockNotification($product));

    Notification::assertSentTo($user, LowStockNotification::class, function ($notification) use ($product) {
        $data = $notification->toArray($notification);

        return $data['type'] === 'low_stock'
            && $data['product_id'] === $product->id
            && $data['current_quantity'] === 5
            && $data['min_quantity'] === 10;
    });
});

it('creates supplier installment notification with correct data', function () {
    $user = User::first();
    $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
    $purchase = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'installment_months' => 3,
        'installment_amount' => 1000,
    ]);

    Notification::fake();

    $user->notify(new SupplierInstallmentDueNotification($purchase));

    Notification::assertSentTo($user, SupplierInstallmentDueNotification::class);
});

it('creates filter candle notification with correct data', function () {
    $user = User::first();
    $customer = Customer::factory()->create();
    $filter = WaterFilter::create([
        'filter_model' => 'Model X',
        'address' => 'Test Address',
        'customer_id' => $customer->id,
        'installed_at' => now(),
    ]);

    Notification::fake();

    $user->notify(new FilterCandleNotification($filter, 'شمعة 1', now()->addDays(7)->toDateString()));

    Notification::assertSentTo($user, FilterCandleNotification::class, function ($notification) use ($filter) {
        $data = $notification->toArray($notification);

        return $data['type'] === 'filter_candle'
            && $data['filter_id'] === $filter->id
            && $data['candle_name'] === 'شمعة 1';
    });
});

it('marks notification as read', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 6,
    ]);

    $user->notify(new CustomerInstallmentDueNotification($sale));

    $notification = $user->unreadNotifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->read_at)->toBeNull();

    $response = $this->post(route('notifications.read', $notification->id));

    $response->assertOk();
    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();

    // Create multiple notifications
    for ($i = 0; $i < 3; $i++) {
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'installment_months' => 6,
        ]);
        $user->notify(new CustomerInstallmentDueNotification($sale));
    }

    expect($user->unreadNotifications()->count())->toBe(3);

    $response = $this->post(route('notifications.read-all'));

    $response->assertOk();
    expect($user->unreadNotifications()->count())->toBe(0);
});

it('deletes notification', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 6,
    ]);

    $user->notify(new CustomerInstallmentDueNotification($sale));

    $notification = $user->notifications()->first();
    expect($notification)->not->toBeNull();

    $response = $this->delete(route('notifications.delete', $notification->id));

    $response->assertOk();
    expect($user->notifications()->where('id', $notification->id)->exists())->toBeFalse();
});

it('deletes all read notifications', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();

    // Create and mark some notifications as read
    for ($i = 0; $i < 3; $i++) {
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'installment_months' => 6,
        ]);
        $user->notify(new CustomerInstallmentDueNotification($sale));
    }

    // Mark first 2 as read
    $user->unreadNotifications()->take(2)->get()->each->markAsRead();

    expect($user->readNotifications()->count())->toBe(2);
    expect($user->unreadNotifications()->count())->toBe(1);

    $response = $this->delete(route('notifications.delete-all-read'));

    $response->assertOk();
    expect($user->readNotifications()->count())->toBe(0);
    expect($user->unreadNotifications()->count())->toBe(1);
});

it('deletes all notifications', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();

    // Create multiple notifications
    for ($i = 0; $i < 5; $i++) {
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'installment_months' => 6,
        ]);
        $user->notify(new CustomerInstallmentDueNotification($sale));
    }

    expect($user->notifications()->count())->toBe(5);

    $response = $this->delete(route('notifications.delete-all'));

    $response->assertOk();
    expect($user->notifications()->count())->toBe(0);
});

it('logs activity when marking notification as read', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 6,
    ]);

    $user->notify(new CustomerInstallmentDueNotification($sale));

    $notification = $user->unreadNotifications()->first();

    $this->post(route('notifications.read', $notification->id));

    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $user->id,
        'description' => 'قراءة إشعار',
    ]);
});

it('logs activity when deleting notification', function () {
    $user = auth()->user();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
        'installment_months' => 6,
    ]);

    $user->notify(new CustomerInstallmentDueNotification($sale));

    $notification = $user->notifications()->first();

    $this->delete(route('notifications.delete', $notification->id));

    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $user->id,
        'description' => 'حذف إشعار',
    ]);
});
