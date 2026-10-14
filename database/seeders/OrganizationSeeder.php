<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationStaff;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Default demo organization.
     */
    private const ORGANIZATION_NAME = 'My Organization Name';

    /**
     * Password used by all seeded demo accounts.
     */
    private const DEFAULT_PASSWORD = 'password123';

    /**
     * Seed accounts, one per staff_level.
     *
     * @var array<int, array{name: string, email: string, staff_position: string, staff_level: string}>
     */
    private const STAFF_ACCOUNTS = [
        [
            'name'           => 'System Admin',
            'email'          => 'admin@example.com',
            'staff_position' => 'Admin',
            'staff_level'    => 'Level 1',
        ],
        [
            'name'           => 'Level One Staff',
            'email'          => 'level1@example.com',
            'staff_position' => 'Fleet Manager',
            'staff_level'    => 'Level 1',
        ],
        [
            'name'           => 'Level Two Staff',
            'email'          => 'level2@example.com',
            'staff_position' => 'Fleet Supervisor',
            'staff_level'    => 'Level 2',
        ],
        [
            'name'           => 'Level Three Staff',
            'email'          => 'level3@example.com',
            'staff_position' => 'Operations Officer',
            'staff_level'    => 'Level 3',
        ],
    ];

    public function run(): void
    {
        // Create Organization if not exists
        $organization = Organization::firstOrCreate(
            ['organization_name' => self::ORGANIZATION_NAME]
        );

        // Seed one account per staff_level and link it to the organization.
        foreach (self::STAFF_ACCOUNTS as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name'     => $account['name'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                ]
            );

            OrganizationStaff::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id'         => $user->id,
                ],
                [
                    'staff_position' => $account['staff_position'],
                    'staff_status'   => 'Active',
                    'staff_level'    => $account['staff_level'],
                ]
            );
        }
    }
}
