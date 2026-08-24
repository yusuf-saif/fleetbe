<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationStaff;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Organization if not exists
        $organization = Organization::firstOrCreate(
            ['organization_name' => 'My Organization Name']
        );

        // Create default admin user if not exists
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'), // 🔒 change in .env for security
            ]
        );

        // Link admin user to organization_staffs
        OrganizationStaff::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $adminUser->id,
            ],
            [
                'staff_position' => 'Admin',
                'staff_status'   => 'Active',
                'staff_level'    => 'Level 1',
            ]
        );
    }
}
