<?php

namespace App\Models;

use App\Observers\SaleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SaleObserver::class)]
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'dealer_name',
        'user_name',
        'total_price',
        'payment_type',
        'installment_amount',
        'installment_months',
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
