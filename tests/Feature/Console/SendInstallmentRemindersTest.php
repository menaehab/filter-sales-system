<?php

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\InstallmentDueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('sends reminders to admin users for due unpaid installments only', function () {
    Notification::fake();
    Carbon::setTestNow('2026-03-11 09:00:00');

    $adminRole = Role::create(['name' => 'admin']);

    $firstAdmin = User::factory()->create();
    $secondAdmin = User::factory()->create();
    $regularUser = User::factory()->create();

    $firstAdmin->assignRole($adminRole);
    $secondAdmin->assignRole($adminRole);

    $supplier = Supplier::factory()->create(['name' => 'Due Supplier']);

    $duePurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'down_payment' => 50,
        'installment_amount' => 125,
        'installment_months' => 2,
        'next_installment_date' => now()->addDays(2),
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);

    Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'down_payment' => 50,
        'installment_amount' => 125,
        'installment_months' => 2,
        'next_installment_date' => now()->addDays(5),
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);

    Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'down_payment' => 300,
        'installment_amount' => 0,
        'installment_months' => 2,
        'next_installment_date' => now()->addDay(),
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);

    $this->artisan('installments:remind')
        ->expectsOutput('Sent reminders for 1 installment(s).')
        ->assertExitCode(0);

    Notification::assertSentTo($firstAdmin, InstallmentDueNotification::class, function (InstallmentDueNotification $notification) use ($duePurchase) {
        return $notification->purchase->is($duePurchase);
    });

    Notification::assertSentTo($secondAdmin, InstallmentDueNotification::class, function (InstallmentDueNotification $notification) use ($duePurchase) {
        return $notification->purchase->is($duePurchase);
    });

    Notification::assertNotSentTo($regularUser, InstallmentDueNotification::class);

    Carbon::setTestNow();
});

it('reports when there are no installments due', function () {
    Notification::fake();
    Carbon::setTestNow('2026-03-11 09:00:00');

    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => $user->name,
        'total_price' => 300,
        'payment_type' => 'installment',
        'down_payment' => 50,
        'installment_amount' => 125,
        'installment_months' => 2,
        'next_installment_date' => now()->addDays(10),
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
    ]);

    $this->artisan('installments:remind')
        ->expectsOutput('No installments due.')
        ->assertExitCode(0);

    Notification::assertNothingSent();

    Carbon::setTestNow();
});
