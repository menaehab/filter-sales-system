<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\User;
use App\Notifications\InstallmentDueNotification;
use Illuminate\Console\Command;

class SendInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:remind';

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
        $purchases = Purchase::with('paymentAllocations')
            ->whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->whereRaw('DATE_ADD(created_at, INTERVAL 1 MONTH) <= ?', [now()->addDays(3)])
            ->get()
            ->filter(fn($p) => !$p->isFullyPaid());

        if ($purchases->isEmpty()) {
            $this->info('No installments due.');

            return;
        }

        $admins = User::role('admin')->get();

        foreach ($purchases as $purchase) {
            foreach ($admins as $admin) {
                $admin->notify(new InstallmentDueNotification($purchase));
            }
        }

        $this->info("Sent reminders for {$purchases->count()} installment(s).");
    }
}
