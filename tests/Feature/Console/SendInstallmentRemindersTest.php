<?php

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use App\Models\User;
use App\Notifications\SupplierInstallmentDueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('sends reminders to admin users for due unpaid installments only', function () {
    Notification::fake();
    Carbon::setTestNow('2026-03-11 09:00:00');

    // ensure permissions exist just like the `actAsAdmin` helper does
    (new \Database\Seeders\PermissionSeeder)->run();
    $permissions = Permission::all();

    // create admin role and attach all current permissions
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $adminRole->givePermissionTo($permissions);

    $firstAdmin = User::factory()->create();
    $secondAdmin = User::factory()->create();
    $regularUser = User::factory()->create();

    $firstAdmin->assignRole($adminRole);
    $secondAdmin->assignRole($adminRole);

    // in case we want the users to individually have the permissions as well
    $firstAdmin->givePermissionTo($permissions);
    $secondAdmin->givePermissionTo($permissions);

    $supplier = Supplier::factory()->create(['name' => 'Due Supplier']);

    $duePurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 125,
        'installment_months' => 2,
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);
    $duePurchase->forceFill([
        'created_at' => now()->subMonth()->addDays(2),
        'updated_at' => now()->subMonth()->addDays(2),
    ])->saveQuietly();

    $notDuePurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 125,
        'installment_months' => 2,
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);
    $notDuePurchase->forceFill([
        'created_at' => now()->subMonth()->addDays(5),
        'updated_at' => now()->subMonth()->addDays(5),
    ])->saveQuietly();

    $fullyPaidPurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => 'System',
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 0,
        'installment_months' => 2,
        'user_id' => $firstAdmin->id,
        'supplier_id' => $supplier->id,
    ]);
    $fullyPaidPurchase->forceFill([
        'created_at' => now()->subMonth()->addDay(),
        'updated_at' => now()->subMonth()->addDay(),
    ])->saveQuietly();

    // make sure the observer can set the user_id by providing an authenticated user
    $this->actingAs($firstAdmin);

    $fullPayment = SupplierPayment::create([
        'amount' => 300,
        'payment_method' => 'cash',
        'supplier_id' => $supplier->id,
        // user_id will be filled by the observer via auth()->id()
    ]);

    SupplierPaymentAllocation::create([
        'amount' => 300,
        'supplier_payment_id' => $fullPayment->id,
        'purchase_id' => $fullyPaidPurchase->id,
    ]);

    $this->artisan('suppliers:installments-remind')
        ->expectsOutput('Sent reminders for 1 installment(s).')
        ->assertExitCode(0);

    Notification::assertSentTo($firstAdmin, SupplierInstallmentDueNotification::class, function (SupplierInstallmentDueNotification $notification) use ($duePurchase) {
        return $notification->purchase->is($duePurchase);
    });

    Notification::assertSentTo($secondAdmin, SupplierInstallmentDueNotification::class, function (SupplierInstallmentDueNotification $notification) use ($duePurchase) {
        return $notification->purchase->is($duePurchase);
    });

    Notification::assertNotSentTo($regularUser, SupplierInstallmentDueNotification::class);

    Carbon::setTestNow();
});

it('reports when there are no installments due', function () {
    Notification::fake();
    Carbon::setTestNow('2026-03-11 09:00:00');

    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $futurePurchase = Purchase::create([
        'supplier_name' => $supplier->name,
        'user_name' => $user->name,
        'total_price' => 300,
        'payment_type' => 'installment',
        'installment_amount' => 125,
        'installment_months' => 2,
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
    ]);
    $futurePurchase->forceFill([
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ])->saveQuietly();

    $this->artisan('suppliers:installments-remind')
        ->expectsOutput('No installments due.')
        ->assertExitCode(0);

    Notification::assertNothingSent();

    Carbon::setTestNow();
});
