<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'approved_by_id',
        'checked_by_id',
        'previous_id',
        'current_request',
        'requestable_type',
        'requestable_id',
        'quantity_requested',
        'quantity_approved',
        'vehicle_odometer',
        'current_fuel_level',
        'maintenance_type',
        'description',
        'status',
        'with_sparepart',
        'spare_part_id',
        'other_sparepart',
    ];

    // Polymorphic relation
    public function requestable()
    {
        return $this->morphTo();
    }

    // History chain
    public function previous()
    {
        return $this->belongsTo(Requests::class, 'previous_id');
    }

    public function next()
    {
        return $this->hasOne(Requests::class, 'previous_id');
    }

    // Relations
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function driver()
    {
        return $this->belongsTo(Drivers::class);
    }
    public function approvedBy()
    {
        return $this->belongsTo(OrganizationStaff::class, 'approved_by_id');
    }
    public function checkedBy()
    {
        return $this->belongsTo(OrganizationStaff::class, 'checked_by_id');
    }

    /**
     * Relationship with SparePart
     */
    public function sparePart()
    {
        return $this->belongsTo(SparePart::class);
    }

    /**
     * Helper: return the actual spare part (existing or custom text)
     */
    public function getSparePartNameAttribute()
    {
        if ($this->sparePart) {
            return $this->sparePart->title;
        }

        return $this->other_sparepart;
    }
}
