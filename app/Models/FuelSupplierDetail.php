<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelSupplierDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'fuel_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function fuel()
    {
        return $this->belongsTo(Fuel::class);
    }
}
