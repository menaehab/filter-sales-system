<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Customer extends Model
{
    use HasSlug,HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'phone',
        'national_number',
        'address',
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

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function getTotalSalesAttribute(): float
    {
        return (float) $this->sales->sum('total_price');
    }

    public function getTotalPaymentsAttribute(): float
    {
        return (float) $this->payments()
            ->where('payment_method', '!=', 'customer_credit')
            ->sum('amount');
    }

    public function getAvailableCreditAttribute(): float
    {
        return abs(min(0, $this->balance));
    }

    public function getBalanceAttribute(): float
    {
        // Balance = Total Sales - Total Payments.
        // Positive = Customer still owes us, Negative = Customer has credit.
        return $this->total_sales - $this->total_payments;
    }
}
