<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'starting_odometer',
        'assigned_by_id',
        'assigned_at',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
        'starting_odometer' => 'decimal:2',
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(OrganizationStaff::class, 'assigned_by_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(OrganizationStaff::class, 'assigned_to_id');
    }
}
