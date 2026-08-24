<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Drivers extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'next_kin_name',
        'next_kin_relationship',
        'next_kin_phone',
        'next_kin_residential_address',
        'next_kin_email',
        'blood_group',
        'genotype',
        'allergies',
        'medical_challenge',
        'eye_condition',
        'residential_address',
        'organization_id',
        'home_address',
        'state',
        'lga',
        'town',
        'nationality',
        'state_of_origin',
        'lga_of_origin',
        'town_of_origin',
        'date_of_birth',
        'driver_license',
        'license_expiry_date',
        'must_change_password',
        'password_reset_pin',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_pin',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'date_of_birth' => 'date',
        'license_expiry_date' => 'date',
        'password_reset_expires_at' => 'datetime',
        'allergies' => 'array',
        'medical_challenge' => 'array',
        'eye_condition' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // public function vehicles()
    // {
    //     return $this->hasMany(Vehicle::class);
    // }
}
