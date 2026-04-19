<?php

namespace App\Models;

use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Model;

class ServiceVisit extends Model
{
    use HasLogActivity;

    protected $fillable = [
        'user_name',
        'maintenance_type',
        'technician_name',
        'cost',
        'notes',
        'user_id',
        'water_filter_id',
        'is_completed',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_completed' => 'boolean',
    ];

    public function waterFilter()
    {
        return $this->belongsTo(WaterFilter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompletionStatus($query, string $status)
    {
        return match ($status) {
            'completed' => $query->where('is_completed', true),
            'pending' => $query->where('is_completed', false),
            default => $query,
        };
    }
}
