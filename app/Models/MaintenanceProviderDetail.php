<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceProviderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'maintenance_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }
}
