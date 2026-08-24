<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fuel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'fuel_type',
        'reserve_level',
        'unit',
        'quantity_allocated',
        'quantity_remaining',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function storages()
    {
        return $this->morphMany(Storage::class, 'storable');
    }
}
