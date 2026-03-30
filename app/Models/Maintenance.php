<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost',
        'description',
        'user_id',
        'water_filter_id',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filter()
    {
        return $this->belongsTo(WaterFilter::class);
    }

    public function items()
    {
        return $this->hasMany(MaintenanceItem::class);
    }
}
