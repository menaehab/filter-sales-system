<?php

namespace App\Models;

use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Model;

class WaterReading extends Model
{
    use HasLogActivity;
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
