<?php

namespace App\Notifications;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InstallmentDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Purchase $purchase,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_id' => $this->purchase->id,
            'supplier_name' => $this->purchase->supplier_name,
            'installment_amount' => $this->purchase->installment_amount,
            'remaining_amount' => $this->purchase->remaining_amount,
            'next_installment_date' => $this->purchase->next_installment_date?->toDateString(),
            'message' => "قسط مستحق للمورد {$this->purchase->supplier_name} بمبلغ {$this->purchase->installment_amount} ج.م",
        ];
    }
}
