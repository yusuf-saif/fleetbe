<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Drivers;
use App\Models\Organization;
use App\Models\OrganizationStaff;
use App\Models\Vehicle;
use App\Models\Fuel;
use App\Models\VehicleAssignment;
use App\Models\Requests as ResourceRequest;

class FleetApiQATest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected User $staffUser;
    protected Drivers $driver;
    protected string $staffToken;
    protected string $driverToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed base organization
        $this->organization = Organization::create([
            'organization_name' => 'QA Test Organization',
        ]);

        // Create staff user
        $this->staffUser = User::create([
            'name'     => 'Staff Manager',
            'email'    => 'manager@example.com',
            'password' => bcrypt('password123'),
        ]);

        OrganizationStaff::create([
            'organization_id' => $this->organization->id,
            'user_id'         => $this->staffUser->id,
            'staff_position'  => 'Fleet Manager',
            'staff_status'    => 'Active',
            'staff_level'     => 'Level 1',
        ]);

        $this->staffToken = $this->staffUser->createToken('staff_token')->plainTextToken;

        // Create driver
        $this->driver = Drivers::create([
            'organization_id'              => $this->organization->id,
            'name'                         => 'Driver Test',
            'email'                        => 'driver@example.com',
            'phone_number'                 => '08012345678',
            'driver_license'               => 'LIC-123456',
            'license_expiry_date'          => now()->addYears(2)->toDateString(),
            'next_kin_name'                => 'Kin Test',
            'next_kin_relationship'        => 'Brother',
            'next_kin_phone'               => '08098765432',
            'next_kin_residential_address' => '123 Kin St',
            'password'                     => bcrypt('password123'),
        ]);

        $this->driverToken = $this->driver->createToken('driver_token')->plainTextToken;
    }

    public function test_auth_registration_creates_user_and_links_staff(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'New Admin',
            'email'                 => 'admin_qa@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'organization_id'       => $this->organization->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User registered successfully')
            ->assertJsonPath('user.email', 'admin_qa@example.com')
            ->assertJsonPath('user.organization_staff.organization_id', $this->organization->id);

        $this->assertDatabaseHas('users', ['email' => 'admin_qa@example.com']);
        $this->assertDatabaseHas('organization_staffs', [
            'organization_id' => $this->organization->id,
            'staff_position'  => 'Fleet Manager',
        ]);
    }

    public function test_driver_registration_with_valid_organization(): void
    {
        $response = $this->postJson('/api/v1/driver/register', [
            'name'                => 'New Driver',
            'email'               => 'newdriver@example.com',
            'phone_number'        => '08099887766',
            'driver_license'      => 'DL-987654',
            'license_expiry_date' => '2028-01-01',
            'organization_id'     => $this->organization->id,
            'password'            => 'secret123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('drivers', ['email' => 'newdriver@example.com']);
    }

    public function test_driver_registration_with_invalid_organization_returns_422(): void
    {
        $response = $this->postJson('/api/v1/driver/register', [
            'name'                => 'Invalid Driver',
            'email'               => 'invalid_org@example.com',
            'phone_number'        => '08011223344',
            'driver_license'      => 'DL-000000',
            'license_expiry_date' => '2028-01-01',
            'organization_id'     => 999999,
            'password'            => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_id']);
    }

    public function test_token_revocation_on_staff_and_driver_logout(): void
    {
        // Staff logout
        $staffLogout = $this->withHeader('Authorization', 'Bearer ' . $this->staffToken)
            ->postJson('/api/v1/auth/logout');

        $staffLogout->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully');

        // Flush test authentication state to force Sanctum re-check
        $this->app['auth']->forgetGuards();

        // Old token must now be invalid
        $afterLogoutStaff = $this->withHeader('Authorization', 'Bearer ' . $this->staffToken)
            ->getJson('/api/v1/vehicles');
        $afterLogoutStaff->assertStatus(401);

        // Driver logout
        $driverLogout = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->postJson('/api/v1/driver/logout');

        $driverLogout->assertStatus(200)
            ->assertJsonPath('message', 'Logged out');

        $this->app['auth']->forgetGuards();

        // Old driver token must now be invalid on authenticated driver routes
        $afterLogoutDriver = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->getJson('/api/v1/driver');
        $afterLogoutDriver->assertStatus(401);
    }

    public function test_driver_can_read_own_assignments_fuel_and_vehicles_but_cannot_mutate(): void
    {
        $vehicle = Vehicle::create([
            'name'                    => 'Toyota Hilux QA Read',
            'type'                    => 'Van',
            'plate_number'            => 'QA-301-AB',
            'chasis_number'           => 'CHS-QA-301',
            'manufacturer'            => 'Toyota',
            'organization_id'         => $this->organization->id,
            'status'                  => 'Unassigned',
            'asset_number'            => 'AST-QA-301',
            'vehicle_security_number' => 'SEC-QA-301',
        ]);

        $fuel = Fuel::create([
            'title'              => 'Diesel Tank Read',
            'fuel_type'          => 'diesel',
            'reserve_level'      => 100,
            'unit'               => 'l',
            'quantity_allocated' => 500,
            'quantity_remaining' => 450,
            'organization_id'    => $this->organization->id,
        ]);

        // Driver CAN read driver assignments
        $assignResponse = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->getJson("/api/v1/vehicle-assignments/driver/{$this->driver->id}");
        $assignResponse->assertStatus(200);

        // Driver CAN read fuel options
        $fuelResponse = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->getJson('/api/v1/fuel');
        $fuelResponse->assertStatus(200);

        // Driver CAN read vehicle details
        $vehicleResponse = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->getJson("/api/v1/vehicles/{$vehicle->id}");
        $vehicleResponse->assertStatus(200);

        // Driver CANNOT mutate vehicles (protected by staff middleware)
        $mutateResponse = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->postJson('/api/v1/vehicles', [
                'name' => 'Should Fail',
            ]);
        $mutateResponse->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden: This resource requires corporate staff privileges.');

        // Driver CANNOT mutate fuel
        $mutateFuelResponse = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->postJson('/api/v1/fuel', [
                'title' => 'Should Fail',
            ]);
        $mutateFuelResponse->assertStatus(403);
    }

    public function test_resource_request_creation_with_omitted_with_sparepart_succeeds(): void
    {
        $vehicle = Vehicle::create([
            'name'                    => 'Toyota Hilux QA',
            'type'                    => 'Van',
            'plate_number'            => 'QA-101-AB',
            'chasis_number'           => 'CHS-QA-101',
            'manufacturer'            => 'Toyota',
            'organization_id'         => $this->organization->id,
            'status'                  => 'Unassigned',
            'asset_number'            => 'AST-QA-101',
            'vehicle_security_number' => 'SEC-QA-101',
        ]);

        $fuel = Fuel::create([
            'title'              => 'Diesel Tank A',
            'fuel_type'          => 'diesel',
            'reserve_level'      => 100,
            'unit'               => 'l',
            'quantity_allocated' => 500,
            'quantity_remaining' => 450,
            'organization_id'    => $this->organization->id,
        ]);

        // with_sparepart is completely omitted in payload
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->driverToken)
            ->postJson('/api/v1/requests', [
                'vehicle_id'         => $vehicle->id,
                'driver_id'          => $this->driver->id,
                'requestable_type'   => 'fuel',
                'requestable_id'     => $fuel->id,
                'quantity_requested' => 45.0,
                'vehicle_odometer'   => 12540.0,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('requestable_type', 'fuel')
            ->assertJsonPath('with_sparepart', false);

        $this->assertDatabaseHas('requests', [
            'vehicle_id' => $vehicle->id,
            'driver_id'  => $this->driver->id,
            'with_sparepart' => false,
        ]);
    }

    public function test_resource_request_approval_audits_approving_staff(): void
    {
        $vehicle = Vehicle::create([
            'name'                    => 'Toyota Hilux QA 2',
            'type'                    => 'Car',
            'plate_number'            => 'QA-102-AB',
            'chasis_number'           => 'CHS-QA-102',
            'manufacturer'            => 'Toyota',
            'organization_id'         => $this->organization->id,
            'status'                  => 'Unassigned',
            'asset_number'            => 'AST-QA-102',
            'vehicle_security_number' => 'SEC-QA-102',
        ]);

        $fuel = Fuel::create([
            'title'              => 'Petrol Tank B',
            'fuel_type'          => 'petrol',
            'reserve_level'      => 50,
            'unit'               => 'l',
            'quantity_allocated' => 400,
            'quantity_remaining' => 360,
            'organization_id'    => $this->organization->id,
        ]);

        $req = ResourceRequest::create([
            'vehicle_id'         => $vehicle->id,
            'driver_id'          => $this->driver->id,
            'requestable_type'   => 'fuel',
            'requestable_id'     => $fuel->id,
            'quantity_requested' => 40,
            'with_sparepart'     => false,
            'status'             => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->staffToken)
            ->postJson("/api/v1/requests/{$req->id}/approve", [
                'quantity_approved' => 40,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'approved');

        $staffProfile = $this->staffUser->organizationStaff;
        $this->assertDatabaseHas('requests', [
            'id'             => $req->id,
            'status'         => 'approved',
            'approved_by_id' => $staffProfile->id,
        ]);
    }

    public function test_vehicle_assignment_blocks_double_assignment(): void
    {
        $vehicle = Vehicle::create([
            'name'                    => 'Ford Transit',
            'type'                    => 'Van',
            'plate_number'            => 'QA-202-CD',
            'chasis_number'           => 'CHS-QA-202',
            'manufacturer'            => 'Ford',
            'organization_id'         => $this->organization->id,
            'status'                  => 'Unassigned',
            'asset_number'            => 'AST-QA-202',
            'vehicle_security_number' => 'SEC-QA-202',
        ]);

        // First assignment
        $first = $this->withHeader('Authorization', 'Bearer ' . $this->staffToken)
            ->postJson('/api/v1/vehicle-assignments', [
                'vehicle_id'  => $vehicle->id,
                'driver_id'   => $this->driver->id,
                'assigned_at' => now()->toDateTimeString(),
            ]);

        $first->assertStatus(201);
        $this->assertEquals('Assigned', $vehicle->fresh()->status);

        // Second assignment while vehicle is still assigned
        $second = $this->withHeader('Authorization', 'Bearer ' . $this->staffToken)
            ->postJson('/api/v1/vehicle-assignments', [
                'vehicle_id'  => $vehicle->id,
                'driver_id'   => $this->driver->id,
                'assigned_at' => now()->toDateTimeString(),
            ]);

        $second->assertStatus(409)
            ->assertJsonPath('message', 'This vehicle is already assigned and cannot be reassigned until released.');
    }

    public function test_swagger_documentation_routes_are_accessible(): void
    {
        $response = $this->get('/swagger');
        $response->assertStatus(200);

        $responseDocs = $this->get('/api/documentation');
        $responseDocs->assertStatus(200);
    }
}
