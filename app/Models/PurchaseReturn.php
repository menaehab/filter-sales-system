<?php

namespace App\Models;

use App\Observers\PurchaseReturnObserver;
use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(PurchaseReturnObserver::class)]
class PurchaseReturn extends Model
{
    use HasFactory, HasLogActivity;

    protected $fillable = [
        'number',
        'total_price',
        'reason',
        'cash_refund',
        'purchase_id',
        'user_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
