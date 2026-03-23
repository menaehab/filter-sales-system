<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $productName = $this->product->name;
        $currentQuantity = $this->product->quantity;
        $minQuantity = $this->product->min_quantity;

        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'product_slug' => $this->product->slug,
            'product_name' => $productName,
            'current_quantity' => $currentQuantity,
            'min_quantity' => $minQuantity,
            'message' => __('keywords.notification_low_stock_message', [
                'product' => $productName,
                'current' => $currentQuantity,
                'min' => $minQuantity,
            ]),
        ];
    }
}
