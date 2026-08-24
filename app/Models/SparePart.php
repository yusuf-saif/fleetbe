<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'size',
        'description',
        'reserve_quantity',
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

    public function requests()
    {
        return $this->hasMany(Requests::class);
    }
}
