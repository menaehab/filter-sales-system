<?php

namespace App\Models;

use App\Observers\SupplierPaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SupplierPaymentObserver::class)]
class SupplierPayment extends Model
{
    protected $fillable = [
        'amount',
        'payment_method',
        'note',
        'user_id',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function allocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
