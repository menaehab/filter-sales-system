<?php

namespace App\Models;

use App\Observers\PurchaseObserver;
use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(PurchaseObserver::class)]
class Purchase extends Model
{
    use HasFactory, HasLogActivity;

    protected $fillable = [
        'number',
        'supplier_name',
        'user_name',
        'total_price',
        'payment_type',
        'installment_amount',
        'installment_months',
        'user_id',
        'supplier_id',
        'created_at',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
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
            ->whereHas('supplierPayment', function ($query) {
                $query->where('payment_method', '!=', 'supplier_credit');
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
        return $this->installment_months > 0;
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

    public function movements()
    {
        return $this->morphMany(ProductMovement::class, 'movable');
    }
}
