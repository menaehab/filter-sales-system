<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'customer_payment_id',
        'sale_id',
    ];

    public function customerPayment()
    {
        return $this->belongsTo(CustomerPayment::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
