<?php

namespace App\Models;

use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamagedProduct extends Model
{
    use HasFactory, HasLogActivity;

    protected $fillable = [
        'product_id',
        'cost_price',
        'quantity',
        'reason',
        'user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->morphMany(ProductMovement::class, 'movable');
    }
}
