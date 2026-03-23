<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;

class SendLowStockAlerts extends Command
{
    protected $signature = 'products:low-stock-alert';

    protected $description = 'Send notifications for products with low stock levels';

    public function handle(): void
    {
        $lowStockProducts = Product::query()
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->where('min_quantity', '>', 0)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low stock products found.');

            return;
        }

        $users = User::all();
        $notificationCount = 0;

        foreach ($lowStockProducts as $product) {
            foreach ($users as $user) {
                $user->notify(new LowStockNotification($product));
                $notificationCount++;
            }

            // Log activity for each low stock notification
            activity()
                ->withProperties([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_quantity' => $product->quantity,
                    'min_quantity' => $product->min_quantity,
                    'notified_users_count' => $users->count(),
                ])
                ->log(__('keywords.activity_send_low_stock_alert'));
        }

        $this->info("Sent {$notificationCount} low stock notification(s).");
    }
}
