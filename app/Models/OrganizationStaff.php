<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationStaff extends Model
{
    use HasFactory;
    protected $table = 'organization_staffs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'staff_position',
        'staff_status',
        'staff_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
