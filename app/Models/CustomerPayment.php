<?php

namespace App\Models;

use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory, HasLogActivity;

    protected $fillable = [
        'amount',
        'payment_method',
        'note',
        'customer_id',
        'user_id',
        'created_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
