<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $fillable = ['name', 'phone'];

    public function waterReadings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaterReading::class);
    }

    public function maintenances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function serviceVisits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceVisit::class);
    }
}
