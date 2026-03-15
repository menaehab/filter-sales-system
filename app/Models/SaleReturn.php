<?php

namespace App\Models;

use App\Observers\SaleReturnObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SaleReturnObserver::class)]
class SaleReturn extends Model
{
    protected $fillable = [
        'number',
        'total_price',
        'reason',
        'cash_refund',
        'sale_id',
        'user_id',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function getRouteKeyName()
    {
        return 'number';
    }
}
