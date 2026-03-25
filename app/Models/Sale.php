<?php

namespace App\Models;

use App\Observers\SaleObserver;
use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SaleObserver::class)]
class Sale extends Model
{
    use HasFactory, HasLogActivity;

    protected $casts = [
        'with_vat' => 'boolean',
    ];

    protected $fillable = [
        'number',
        'dealer_name',
        'user_name',
        'total_price',
        'payment_type',
        'discount_value',
        'interest_rate',
        'installment_amount',
        'installment_months',
        'with_vat',
        'customer_id',
        'user_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->paymentAllocations->sum('amount');
    }

    public function getItemsSubtotalAttribute(): float
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return (float) $items->map(function ($item) {
            return (float) $item->sell_price * (float) $item->quantity;
        })->sum();
    }

    public function getVatAmountAttribute(): float
    {
        if (! $this->with_vat) {
            return 0;
        }

        return round($this->items_subtotal * 0.14, 2);
    }

    public function getSubtotalAfterVatAttribute(): float
    {
        return $this->items_subtotal + $this->vat_amount;
    }

    public function getAppliedCustomerCreditAmountAttribute(): float
    {
        return (float) $this->paymentAllocations()
            ->whereHas('customerPayment', function ($query) {
                $query->where('payment_method', 'customer_credit');
            })
            ->sum('amount');
    }

    public function getInstallmentSurchargeTotalAttribute(): float
    {
        $months = (int) ($this->installment_months ?: 0);
        if (! $this->isInstallment() || $months < 3) {
            return 0;
        }

        return $months * 100;
    }

    public function getInstallmentBaseAmountAttribute(): float
    {
        return max(0, $this->subtotal_after_vat - $this->down_payment - $this->applied_customer_credit_amount);
    }

    public function getInterestAmountAttribute(): float
    {
        if (! $this->isInstallment()) {
            return 0;
        }

        $rate = max(0, (float) ($this->interest_rate ?: 0));

        return round($this->installment_base_amount * ($rate / 100), 2);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_price - $this->paid_amount);
    }

    public function getDownPaymentAttribute(): float
    {
        return (float) $this->paymentAllocations()
            ->whereHas('customerPayment', function ($query) {
                $query->where('payment_method', '!=', 'customer_credit');
            })
            ->orderBy('created_at', 'asc')
            ->first()
            ?->amount ?? 0;
    }

    public function getNextInstallmentDateAttribute()
    {
        if (! $this->isInstallment() || $this->isFullyPaid()) {
            return null;
        }

        return $this->created_at?->addMonth();
    }

    public function isInstallment(): bool
    {
        return (int) $this->installment_months > 0;
    }

    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getPaidInstallmentsCountAttribute(): int
    {
        return $this->paymentAllocations()->count();
    }

    public function getRouteKeyName()
    {
        return 'number';
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function movements()
    {
        return $this->morphMany(ProductMovement::class, 'movable');
    }
}
