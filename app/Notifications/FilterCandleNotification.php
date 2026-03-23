<?php

namespace App\Notifications;

use App\Models\WaterFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FilterCandleNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WaterFilter $filter,
        public string $candleName,
        public ?string $dueDate = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->filter->customer?->name ?? __('keywords.not_specified_arabic');
        $filterModel = $this->filter->filter_model;
        $address = $this->filter->address;

        $message = __('keywords.notification_filter_candle_message', [
            'candle' => $this->candleName,
            'filter' => $filterModel,
            'customer' => $customerName,
            'address' => $address,
        ]);

        return [
            'type' => 'filter_candle',
            'filter_id' => $this->filter->id,
            'filter_slug' => $this->filter->slug,
            'filter_model' => $filterModel,
            'customer_id' => $this->filter->customer_id,
            'customer_name' => $customerName,
            'customer_phone' => $this->filter->customer?->phone,
            'address' => $address,
            'candle_name' => $this->candleName,
            'due_date' => $this->dueDate,
            'message' => $message,
        ];
    }
}
