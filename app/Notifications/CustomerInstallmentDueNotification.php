<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerInstallmentDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Sale $sale,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->sale->customer?->name ?? __('keywords.not_specified_arabic');
        $saleNumber = $this->sale->number;
        $installmentAmount = number_format($this->sale->installment_amount, 2);
        $remainingAmount = number_format($this->sale->remaining_amount, 2);

        return [
            'type' => 'customer_installment',
            'sale_id' => $this->sale->id,
            'sale_number' => $saleNumber,
            'customer_id' => $this->sale->customer_id,
            'customer_name' => $customerName,
            'customer_phone' => $this->sale->customer?->phone,
            'installment_amount' => $this->sale->installment_amount,
            'remaining_amount' => $this->sale->remaining_amount,
            'next_installment_date' => $this->sale->next_installment_date?->toDateString(),
            'message' => __('keywords.notification_customer_installment_message', [
                'customer' => $customerName,
                'amount' => $installmentAmount,
                'sale_number' => $saleNumber,
                'remaining' => $remainingAmount,
            ]),
        ];
    }
}
