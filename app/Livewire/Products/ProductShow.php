<?php

namespace App\Livewire\Products;

use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'product_details'])]
class ProductShow extends Component
{
    use WithPagination;

    public Product $product;
    public int $movementsPerPage = 10;

    public function mount(Product $product)
    {
        $this->product = $product->load('category');
    }

    public function getTotalPurchasedProperty(): int
    {
        return (int) abs($this->product->movements()
            ->where('movable_type', Purchase::class)
            ->where('quantity', '>', 0)
            ->sum('quantity'));
    }

    public function getTotalSoldProperty(): int
    {
        return (int) abs($this->product->movements()
            ->where('movable_type', Sale::class)
            ->where('quantity', '<', 0)
            ->sum('quantity'));
    }

    public function getTotalDamagedProperty(): int
    {
        return (int) abs($this->product->movements()
            ->where('movable_type', DamagedProduct::class)
            ->sum('quantity'));
    }

    public function getTotalSaleReturnsProperty(): int
    {
        return (int) abs($this->product->movements()
            ->where('movable_type', SaleReturn::class)
            ->where('quantity', '>', 0)
            ->sum('quantity'));
    }

    public function getTotalPurchaseReturnsProperty(): int
    {
        return (int) abs($this->product->movements()
            ->where('movable_type', PurchaseReturn::class)
            ->where('quantity', '<', 0)
            ->sum('quantity'));
    }

    public function getCalculatedStockProperty(): int
    {
        return $this->totalPurchased
             - $this->totalSold
             - $this->totalDamaged
             + $this->totalSaleReturns
             - $this->totalPurchaseReturns;
    }

    public function getTotalProfitProperty(): float
    {
        return (float) SaleItem::where('product_id', $this->product->id)
            ->selectRaw('SUM((sell_price - cost_price) * quantity) as profit')
            ->value('profit') ?? 0;
    }

    public function getTotalLossProperty(): float
    {
        return (float) DamagedProduct::where('product_id', $this->product->id)
            ->selectRaw('SUM(cost_price * quantity) as loss')
            ->value('loss') ?? 0;
    }

    public function getNetProfitProperty(): float
    {
        return $this->totalProfit - $this->totalLoss;
    }

    public function getMovementsProperty()
    {
        return $this->product->movements()
            ->with('movable')
            ->latest()
            ->paginate($this->movementsPerPage);
    }

    public function getMovementTypeLabel(string $type): string
    {
        return match ($type) {
            Purchase::class => __('keywords.purchase'),
            Sale::class => __('keywords.sale'),
            DamagedProduct::class => __('keywords.damaged_product'),
            SaleReturn::class => __('keywords.sale_return'),
            PurchaseReturn::class => __('keywords.purchase_return'),
            default => class_basename($type),
        };
    }

    public function getMovementTypeColor(string $type): string
    {
        return match ($type) {
            Purchase::class => 'emerald',
            Sale::class => 'blue',
            DamagedProduct::class => 'red',
            SaleReturn::class => 'amber',
            PurchaseReturn::class => 'purple',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('livewire.products.product-show');
    }
}
