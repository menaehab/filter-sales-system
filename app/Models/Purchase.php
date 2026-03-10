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
        'installment_amount',
        'installment_months',
        'user_id',
        'supplier_id',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }
}
