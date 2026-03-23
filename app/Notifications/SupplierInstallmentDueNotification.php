<?php

namespace App\Notifications;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupplierInstallmentDueNotification extends Notification
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
        $supplierName = $this->purchase->supplier?->name ?? $this->purchase->supplier_name ?? __('keywords.not_specified_arabic');
        $installmentAmount = number_format($this->purchase->installment_amount, 2);
        $remainingAmount = number_format($this->purchase->remaining_amount, 2);

        return [
            'type' => 'supplier_installment',
            'purchase_id' => $this->purchase->id,
            'purchase_number' => $this->purchase->number,
            'supplier_id' => $this->purchase->supplier_id,
            'supplier_name' => $supplierName,
            'installment_amount' => $this->purchase->installment_amount,
            'remaining_amount' => $this->purchase->remaining_amount,
            'next_installment_date' => $this->purchase->next_installment_date?->toDateString(),
            'message' => __('keywords.notification_supplier_installment_message', [
                'supplier' => $supplierName,
                'amount' => $installmentAmount,
                'remaining' => $remainingAmount,
            ]),
        ];
    }
}
