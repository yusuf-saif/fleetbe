<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'total_quantity',
        'storable_id',
        'storable_type',
    ];

    public function storable()
    {
        return $this->morphTo();
    }
}
