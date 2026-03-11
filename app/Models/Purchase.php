<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_name',
        'user_name',
        'total_price',
        'payment_type',
        'down_payment',
        'installment_amount',
        'installment_months',
        'next_installment_date',
        'user_id',
        'supplier_id',
    ];

    protected function casts(): array
    {
        return [
            'next_installment_date' => 'date',
        ];
    }

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

    public function paymentAllocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->down_payment + $this->paymentAllocations->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_price - $this->paid_amount);
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
}
