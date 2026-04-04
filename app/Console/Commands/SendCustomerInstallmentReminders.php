<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\User;
use App\Notifications\CustomerInstallmentDueNotification;
use Illuminate\Console\Command;

class SendCustomerInstallmentReminders extends Command
{
    protected $signature = 'customers:installment-remind';

    protected $description = 'Send notifications for customer installments that are due';

    public function handle(): void
    {
        /** @var \Illuminate\Support\Collection<int, Sale> $dueInstallments */
        $dueInstallments = Sale::query()
            ->whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->with('customer')
            ->get()
            ->filter(function (Sale $sale): bool {
                return ! $sale->isFullyPaid()
                    && $sale->next_installment_date
                    && $sale->next_installment_date->lte(now());
            });

        if ($dueInstallments->isEmpty()) {
            $this->info('No due customer installments found.');

            return;
        }

        /** @var \Illuminate\Support\Collection<int, User> $users */
        $users = User::all()
            ->filter(fn (User $user): bool => $user->can('receive_customer_installment_notifications'))
            ->values();

        if ($users->isEmpty()) {
            $this->warn('No users with customer installment notification permission found.');

            return;
        }

        $notificationCount = 0;

        foreach ($dueInstallments as $sale) {
            foreach ($users as $user) {
                $user->notify(new CustomerInstallmentDueNotification($sale));
                $notificationCount++;
            }

            // Log activity for each installment notification
            activity()
                ->event('activity_send_customer_installment_reminder')
                ->withProperties([
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->number,
                    'customer_name' => $sale->customer?->name,
                    'installment_amount' => $sale->installment_amount,
                    'remaining_amount' => $sale->remaining_amount,
                    'notified_users_count' => $users->count(),
                ])
                ->log(__('keywords.activity_send_customer_installment_reminder'));
        }

        $this->info("Sent {$notificationCount} customer installment notification(s).");
    }
}
