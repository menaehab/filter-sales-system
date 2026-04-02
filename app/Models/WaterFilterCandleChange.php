<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterFilterCandleChange extends Model
{
    protected $fillable = [
        'maintenance_id',
        'water_filter_id',
        'user_id',
        'candle_key',
        'candle_name',
        'replaced_at',
    ];

    protected $casts = [
        'replaced_at' => 'datetime',
    ];

    public function waterFilter()
    {
        return $this->belongsTo(WaterFilter::class);
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
