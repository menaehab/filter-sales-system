<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    protected $fillable = [
        'maintenance_id',
        'quantity',
        'sale_item_id',
    ];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
