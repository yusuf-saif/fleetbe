<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartSupplierDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'spare_part_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class);
    }
}
