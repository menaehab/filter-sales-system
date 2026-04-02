<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\User;
use App\Notifications\SupplierInstallmentDueNotification;
use Illuminate\Console\Command;

class SendSupplierInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suppliers:installments-remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for upcoming installment payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \Illuminate\Support\Collection<int, Purchase> $purchases */
        $purchases = Purchase::with(['paymentAllocations', 'supplier'])
            ->whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->whereRaw('DATE_ADD(created_at, INTERVAL 1 MONTH) <= ?', [now()->addDays(3)])
            ->get()
            ->filter(fn ($purchase): bool => $purchase instanceof Purchase && ! $purchase->isFullyPaid())
            ->values();

        if ($purchases->isEmpty()) {
            $this->info('No installments due.');

            return;
        }

        /** @var \Illuminate\Support\Collection<int, User> $users */
        $users = User::all()
            ->filter(fn (User $user): bool => $user->can('receive_supplier_installment_notifications'))
            ->values();

        if ($users->isEmpty()) {
            $this->warn('No users with supplier installment notification permission found.');

            return;
        }

        foreach ($purchases as $purchase) {
            if (! $purchase instanceof Purchase) {
                continue;
            }

            foreach ($users as $user) {
                $user->notify(new SupplierInstallmentDueNotification($purchase));
            }

            activity()
                ->event('activity_send_supplier_installment_reminder')
                ->withProperties([
                    'purchase_id' => $purchase->id,
                    'purchase_number' => $purchase->number,
                    'supplier_name' => $purchase->supplier?->name ?? $purchase->supplier_name,
                    'installment_amount' => $purchase->installment_amount,
                    'remaining_amount' => $purchase->remaining_amount,
                    'notified_users_count' => $users->count(),
                ])
                ->log(__('keywords.activity_send_supplier_installment_reminder'));
        }

        $this->info("Sent reminders for {$purchases->count()} installment(s).");
    }
}
