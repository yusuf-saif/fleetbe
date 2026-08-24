<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'plate_number',
        'chasis_number',
        'manufacturer',
        'condition',
        'status',
        'fuel_capacity',
        'organization_id',
        'driver_id',
        'user_assigned_id',
        'asset_number',
        'vehicle_security_number',
        'date_purchased',
        'manufactured_year',
        'fuel_type',
    ];

    protected $casts = [
        'manufactured_year' => 'integer',
        'date_purchased' => 'date',
        'fuel_type' => 'array'
    ];

    /**
     * Relationships
     */

    // Vehicle belongs to an organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Vehicle may have a driver assigned
    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }

    // Vehicle may be assigned to a staff
    public function assignedUser()
    {
        return $this->belongsTo(OrganizationStaff::class, 'user_assigned_id');
    }
}
