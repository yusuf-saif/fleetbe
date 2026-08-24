<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'location',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
    ];

    // Relationships
    public function fuelDetails()
    {
        return $this->hasMany(FuelSupplierDetail::class);
    }

    public function sparePartDetails()
    {
        return $this->hasMany(SparePartSupplierDetail::class);
    }

    public function maintenanceProviderDetails()
    {
        return $this->hasMany(MaintenanceProviderDetail::class);
    }
}
