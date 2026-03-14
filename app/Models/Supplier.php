<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Supplier extends Model
{
    use HasSlug,HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'phone',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->purchases->sum('total_price');
    }

    public function getTotalPaymentsAttribute(): float
    {
        return (float) $this->payments()
            ->where('payment_method', '!=', 'supplier_credit')
            ->sum('amount');
    }

    public function getTotalReturnsWithoutCashAttribute(): float
    {
        // Sum of purchase returns where cash_refund = false
        return (float) PurchaseReturn::whereIn('purchase_id', $this->purchases()->pluck('id'))
            ->where('cash_refund', false)
            ->sum('total_price');
    }

    public function getAvailableCreditAttribute(): float
    {
        return abs(min(0, $this->balance));
    }

    public function getBalanceAttribute(): float
    {
        // Balance = Total Purchases - Total Payments - Returns (without cash refund)
        // Positive = We still owe the supplier, Negative = Supplier owes us credit
        return $this->total_purchases - $this->total_payments - $this->total_returns_without_cash;
    }
}
