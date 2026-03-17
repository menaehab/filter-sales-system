<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterReading extends Model
{
    protected $fillable = [
        'technician_name',
        'tds',
        'water_quality',
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
