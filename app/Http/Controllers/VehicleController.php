<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

/**
 * @group Vehicles
 *
 * @authenticated
 * APIs for managing vehicles
 */
class VehicleController extends Controller
{
    /**
     * Get all vehicles
     *
     * Fetch a list of all vehicles with their organization, driver, and assigned staff.
     *
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Toyota Hiace",
     *       "type": "Van",
     *       "plate_number": "ABC123",
     *       "chasis_number": "CHS123456789",
     *       "manufacturer": "Toyota",
     *       "condition": "Used",
     *       "status": "Active",
     *       "fuel_capacity": "70.00",
     *       "organization": { "id": 1, "name": "Org Ltd" },
     *       "driver": { "id": 2, "name": "John Doe" },
     *       "assigned_user": { "id": 3, "name": "Jane Doe" }
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $vehicles = Vehicle::with(['organization', 'driver', 'assignedUser'])->get();

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    /**
     * Create a new vehicle
     *
     * Store a new vehicle in the system.
     *
     * @bodyParam name string required The vehicle name. Example: Toyota Hiace
     * @bodyParam type string required The type of vehicle (Car, Van, Truck, Bus). Example: Van
     * @bodyParam plate_number string required Unique plate number. Example: ABC123
     * @bodyParam chasis_number string required Unique chassis number. Example: CHS123456789
     * @bodyParam manufacturer string required Vehicle manufacturer. Example: Toyota
     * @bodyParam condition string required Vehicle condition (In Good Condition, Flagged for Repair, Damaged). Example: Used
     * @bodyParam status string required Current status (Assigned Unassigned, Under Maintenance, Parked). Example: Unassigned
     * @bodyParam fuel_capacity number Fuel tank capacity. Example: 70.5
     * @bodyParam organization_id int required The owning organization ID. Example: 1
     * @bodyParam driver_id int The assigned driver ID (nullable). Example: 2
     * @bodyParam user_assigned_id int The assigned staff ID (nullable). Example: 3
     * @bodyParam asset_number string required Unique internal asset number. Example: ASSET-2025-001
     * @bodyParam vehicle_security_number string required Unique vehicle security identifier (chassis/engine). Example: CHS123456789
     * @bodyParam date_purchased date The purchase date of the vehicle. Example: 2023-06-15
     * @bodyParam manufactured_year integer The year the vehicle was manufactured. Example: 2020
     * @bodyParam fuel_type string The fuel type of the vehicle. Must be one of: petrol, diesel, electric, hybrid. Example: diesel
     *
     * @response 201 {
     *   "id": 1,
     *   "asset_number": "ASSET-2025-001",
     *   "vehicle_security_number": "CHS123456789",
     *   "date_purchased": "2023-06-15",
     *   "manufactured_year": 2020,
     *   "fuel_type": "diesel",
     *   "created_at": "2025-09-20T10:00:00.000000Z",
     *   "updated_at": "2025-09-20T10:00:00.000000Z"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:Car,Van,Truck,Bus',
            'plate_number' => 'required|string|unique:vehicles',
            'chasis_number' => 'required|string|unique:vehicles',
            'manufacturer' => 'required|string',
            'condition' => 'required|in:In Good Condition,Flagged for Repair,Damaged',
            'status' => 'nullable|in:Assigned,Unassigned,Under Maintenance,Parked',
            'fuel_capacity' => 'nullable|numeric|min:0',
            'organization_id' => 'required|exists:organizations,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'user_assigned_id' => 'nullable|exists:organization_staffs,id',
            'asset_number' => 'required|string|unique:vehicles,asset_number',
            'vehicle_security_number' => 'required|string|unique:vehicles,vehicle_security_number',
            'date_purchased' => 'nullable|date',
            'manufactured_year' => 'nullable|digits:4|integer|min:1900|max:'.date('Y'),
            // 'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid',
            'fuel_type'         => 'sometimes|nullable|array',
            'fuel_type.*'       => 'sometimes|string|max:255',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully.',
            'data' => $vehicle,
        ], 201);
    }

    /**
     * Get a single vehicle
     *
     * @urlParam id int required The ID of the vehicle. Example: 1
     *
     * @response 200 scenario="Found" {
     *   "success": true,
     *   "data": { "id": 1, "name": "Toyota Hiace", "type": "Van" }
     * }
     * @response 404 scenario="Not found" {
     *   "message": "No query results for model [Vehicle] 999"
     * }
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['organization', 'driver', 'assignedUser'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    /**
     * Update a vehicle
     *
     * @urlParam id int required The ID of the vehicle. Example: 1
     *
     * @bodyParam name string The vehicle name. Example: Toyota Corolla
     * @bodyParam type string The type of vehicle (Car, Van, Truck, Bus). Example: Car
     * @bodyParam status string The current status. Example: Inactive
     *
     * @response 200 scenario="Updated" {
     *   "success": true,
     *   "message": "Vehicle updated successfully.",
     *   "data": { "id": 1, "name": "Toyota Corolla", "status": "Inactive" }
     * }
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'type' => 'sometimes|in:Car,Van,Truck,Bus',
            'plate_number' => 'sometimes|string|unique:vehicles,plate_number,'.$vehicle->id,
            'chasis_number' => 'sometimes|string|unique:vehicles,chasis_number,'.$vehicle->id,
            'manufacturer' => 'sometimes|string',
            'condition' => 'sometimes|in:In Good Condition,Flagged for Repair,Damaged',
            'status' => 'sometimes|in:Assigned,Unassigned,Under Maintenance,Parked',
            'fuel_capacity' => 'nullable|numeric|min:0',
            'organization_id' => 'sometimes|exists:organizations,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'user_assigned_id' => 'nullable|exists:organization_staffs,id',
            'asset_number' => 'sometimes|string|unique:vehicles,asset_number,'.$vehicle->id,
            'vehicle_security_number' => 'sometimes|string|unique:vehicles,vehicle_security_number,'.$vehicle->id,
            'date_purchased' => 'nullable|date',
            'manufactured_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
            // 'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid',
            'fuel_type'         => 'sometimes|nullable|array',
            'fuel_type.*'       => 'sometimes|string|max:255',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully.',
            'data' => $vehicle,
        ]);
    }

    /**
     * Delete a vehicle
     *
     * @urlParam id int required The ID of the vehicle. Example: 1
     *
     * @response 200 scenario="Deleted" {
     *   "success": true,
     *   "message": "Vehicle deleted successfully."
     * }
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}
