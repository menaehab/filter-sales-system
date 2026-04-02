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
        'before_installment',
        'water_filter_id',
        'created_at',
    ];

    protected $casts = [
        'before_installment' => 'boolean',
        'tds' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (WaterReading $reading) {
            if ($reading->before_installment && $reading->waterFilter) {
                $filter = $reading->waterFilter;
                if (! $filter->installed_at) {
                    $filter->update(['installed_at' => $reading->created_at->toDateString()]);
                }
            }
        });
    }

    public function waterFilter()
    {
        return $this->belongsTo(WaterFilter::class);
    }
}
