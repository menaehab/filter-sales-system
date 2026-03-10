<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAllocation extends Model
{
    protected $fillable = [
        'amount',
        'supplier_payment_id',
        'purchase_id',
    ];

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
