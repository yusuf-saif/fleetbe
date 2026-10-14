<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Drivers;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Demo organization name (matches OrganizationSeeder).
     */
    private const ORGANIZATION_NAME = 'My Organization Name';

    /**
     * Password used by all seeded demo drivers.
     */
    private const DEFAULT_PASSWORD = 'password123';

    /**
     * Demo driver accounts.
     *
     * @var array<int, array<string, string>>
     */
    private const DRIVERS = [
        [
            'name'           => 'John Doe',
            'email'          => 'driver1@example.com',
            'phone_number'   => '08011111111',
            'driver_license' => 'DL-0001',
        ],
        [
            'name'           => 'Ada Obi',
            'email'          => 'driver2@example.com',
            'phone_number'   => '08022222222',
            'driver_license' => 'DL-0002',
        ],
        [
            'name'           => 'Musa Bello',
            'email'          => 'driver3@example.com',
            'phone_number'   => '08033333333',
            'driver_license' => 'DL-0003',
        ],
    ];

    public function run(): void
    {
        $organization = Organization::firstOrCreate(
            ['organization_name' => self::ORGANIZATION_NAME]
        );

        foreach (self::DRIVERS as $driver) {
            Drivers::firstOrCreate(
                ['email' => $driver['email']],
                [
                    'name'                       => $driver['name'],
                    'phone_number'               => $driver['phone_number'],
                    'password'                   => Hash::make(self::DEFAULT_PASSWORD),
                    'organization_id'            => $organization->id,
                    'driver_license'             => $driver['driver_license'],
                    'license_expiry_date'        => '2030-12-31',
                    'next_kin_name'              => 'N/A',
                    'next_kin_relationship'      => 'N/A',
                    'next_kin_phone'             => 'N/A',
                    'next_kin_residential_address' => 'N/A',
                    'must_change_password'       => false,
                ]
            );
        }
    }
}
