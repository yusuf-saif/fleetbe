<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Http\Request;

/**
 * @group Vehicle Assignments
 *
 * APIs for managing vehicle assignments
 *
 * @authenticated
 */
class VehicleAssignmentController extends Controller
{
    /**
     * List vehicle assignments
     *
     * Returns all vehicle assignments. Supports optional filtering by driver or vehicle.
     *
     * @queryParam driver_id int Filter by driver ID. Example: 2
     * @queryParam vehicle_id int Filter by vehicle ID. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "vehicle_id": 5,
     *       "driver_id": 2,
     *       "starting_odometer": "1200.50",
     *       "assigned_by_id": 1,
     *       "assigned_at": "2025-09-11T08:00:00.000000Z",
     *       "released_at": null,
     *       "vehicle": { "id": 5, "plate_number": "ABC123" },
     *       "driver": { "id": 2, "name": "John Doe" },
     *       "assignedBy": { "id": 1, "name": "Admin Staff" }
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = VehicleAssignment::query()->with(['vehicle', 'driver', 'assignedBy']);

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Create a vehicle assignment
     *
     * Assign a vehicle to a driver.
     *
     * @bodyParam vehicle_id int required Vehicle ID. Example: 5
     * @bodyParam driver_id int required Driver ID. Example: 2
     * @bodyParam starting_odometer number nullable Starting odometer. Example: 1200.50
     * @bodyParam assigned_by_id int nullable Staff ID who assigned the vehicle. Example: 1
     * @bodyParam assigned_to_id int nullable Staff ID who assigned the vehicle. Example: 1
     * @bodyParam assigned_at datetime nullable Assignment start time. Example: "2025-09-11 08:00:00"
     * @bodyParam released_at datetime nullable Assignment end time. Example: "2025-09-20 17:00:00"
     *
     * @response 201 {
     *   "id": 1,
     *   "vehicle_id": 5,
     *   "driver_id": 2,
     *   "starting_odometer": "1200.50",
     *   "assigned_by_id": 1,
     *   "assigned_to_id": 2,
     *   "assigned_at": "2025-09-11T08:00:00.000000Z",
     *   "released_at": null,
     *   "vehicle": { "id": 5, "plate_number": "ABC123" },
     *   "driver": { "id": 2, "name": "John Doe" },
     *   "assignedBy": { "id": 1, "name": "Admin Staff" }
     *   "assignedTo": { "id": 1, "name": "Company Staff" }
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'starting_odometer' => 'nullable|numeric|min:0',
            'assigned_by_id' => 'nullable|exists:organization_staffs,id',
            'assigned_to_id' => 'nullable|exists:organization_staffs,id',
            'assigned_at' => 'nullable|date',
            'released_at' => 'nullable|date|after_or_equal:assigned_at',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->status === 'assigned') {
            return response()->json([
                'message' => 'This vehicle is already assigned and cannot be reassigned until released.'
            ], 409);
        }

        $assignment = VehicleAssignment::create($validated);

        $assignment->vehicle()->update([
            'status' => 'Assigned',
        ]);

        return response()->json(
            $assignment->load(['vehicle', 'driver', 'assignedBy']),
            201
        );
    }


    /**
     * Show vehicle assignment
     *
     * Get details of a single assignment.
     *
     * @urlParam id int required Assignment ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "vehicle_id": 5,
     *   "driver_id": 2,
     *   "starting_odometer": "1200.50",
     *   "assigned_by_id": 1,
     *   "assigned_at": "2025-09-11T08:00:00.000000Z",
     *   "released_at": null,
     *   "vehicle": { "id": 5, "plate_number": "ABC123" },
     *   "driver": { "id": 2, "name": "John Doe" },
     *   "assignedBy": { "id": 1, "name": "Admin Staff" }
     * }
     */
    public function show($id)
    {
        $assignment = VehicleAssignment::with(['vehicle', 'driver', 'assignedBy'])->findOrFail($id);
        return response()->json($assignment);
    }

    /**
     * List current active vehicle assignments
     *
     * Returns all assignments that are currently active (released_at is null or in the future)
     *
     * @queryParam driver_id int nullable Filter by driver ID. Example: 2
     * @queryParam vehicle_id int nullable Filter by vehicle ID. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "vehicle_id": 5,
     *       "driver_id": 2,
     *       "starting_odometer": "1200.50",
     *       "assigned_at": "2025-09-11T08:00:00.000000Z",
     *       "released_at": null,
     *       "vehicle": { "id": 5, "plate_number": "ABC123" },
     *       "driver": { "id": 2, "name": "John Doe" }
     *     }
     *   ]
     * }
     */
    public function currentAssignments(Request $request)
    {
        $query = VehicleAssignment::with(['vehicle', 'driver'])
            ->where(function ($q) {
                $q->whereNull('released_at')
                    ->orWhere('released_at', '>', now());
            });

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * List all assignments for a specific driver
     *
     * @urlParam driver_id int required Driver ID. Example: 2
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "vehicle_id": 5,
     *       "driver_id": 2,
     *       "starting_odometer": "1200.50",
     *       "assigned_at": "2025-09-11T08:00:00.000000Z",
     *       "released_at": null,
     *       "vehicle": { "id": 5, "plate_number": "ABC123" }
     *     }
     *   ]
     * }
     */
    public function assignmentsByDriver($driver_id)
    {
        $assignments = VehicleAssignment::with('vehicle')
            ->where('driver_id', $driver_id)
            ->get();

        return response()->json(['data' => $assignments]);
    }

    /**
     * List all assignments for a specific vehicle
     *
     * @urlParam vehicle_id int required Vehicle ID. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "vehicle_id": 5,
     *       "driver_id": 2,
     *       "starting_odometer": "1200.50",
     *       "assigned_at": "2025-09-11T08:00:00.000000Z",
     *       "released_at": null,
     *       "driver": { "id": 2, "name": "John Doe" }
     *     }
     *   ]
     * }
     */
    public function assignmentsByVehicle($vehicle_id)
    {
        $assignments = VehicleAssignment::with('driver')
            ->where('vehicle_id', $vehicle_id)
            ->get();

        return response()->json(['data' => $assignments]);
    }

    /**
     * Release a vehicle assignment
     *
     * Marks the vehicle as available again after its assignment ends.
     *
     * @urlParam assignment integer required The ID of the vehicle assignment. Example: 1
     *
     * @bodyParam released_at date required The date and time the vehicle was released.
     * Must be on or after the assigned_at date. Example: 2025-09-30 14:30:00
     *
     * @response 200 {
     *   "id": 1,
     *   "vehicle_id": 10,
     *   "driver_id": 5,
     *   "starting_odometer": 12000,
     *   "assigned_by_id": 2,
     *   "assigned_to_id": 3,
     *   "assigned_at": "2025-09-29 09:00:00",
     *   "released_at": "2025-09-30 14:30:00",
     *   "vehicle": {
     *     "id": 10,
     *     "title": "Toyota Hilux",
     *     "status": "available"
     *   },
     *   "driver": {
     *     "id": 5,
     *     "name": "John Doe"
     *   },
     *   "assigned_by": {
     *     "id": 2,
     *     "name": "Jane Smith"
     *   }
     * }
     *
     * @response 409 {
     *   "message": "This assignment has already been released."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "released_at": [
     *       "The released at field is required."
     *     ]
     *   }
     * }
     */
    public function release(Request $request, VehicleAssignment $assignment)
    {
        if ($assignment->released_at) {
            return response()->json([
                'message' => 'This assignment has already been released.'
            ], 409);
        }

        $validated = $request->validate([
            'released_at' => 'required|date|after_or_equal:assigned_at',
        ]);

        // Update assignment release time
        $assignment->update([
            'released_at' => $validated['released_at'],
        ]);

        // Mark vehicle as available again
        $assignment->vehicle()->update([
            'status' => 'Unassigned',
        ]);

        return response()->json(
            $assignment->load(['vehicle', 'driver', 'assignedBy']),
            200
        );
    }


    /**
     * Update vehicle assignment
     *
     * Update the assignment details, e.g., released_at.
     *
     * @urlParam id int required Assignment ID. Example: 1
     *
     * @bodyParam starting_odometer number nullable Starting odometer. Example: 1200.50
     * @bodyParam assigned_by_id int nullable Staff ID who assigned the vehicle. Example: 1
     * @bodyParam assigned_at datetime nullable Assignment start time. Example: "2025-09-11 08:00:00"
     * @bodyParam released_at datetime nullable Assignment end time. Example: "2025-09-20 17:00:00"
     *
     * @response 200 {
     *   "id": 1,
     *   "vehicle_id": 5,
     *   "driver_id": 2,
     *   "starting_odometer": "1200.50",
     *   "assigned_by_id": 1,
     *   "assigned_at": "2025-09-11T08:00:00.000000Z",
     *   "released_at": "2025-09-20T17:00:00.000000Z",
     *   "vehicle": { "id": 5, "plate_number": "ABC123" },
     *   "driver": { "id": 2, "name": "John Doe" },
     *   "assignedBy": { "id": 1, "name": "Admin Staff" }
     * }
     */
    public function update(Request $request, $id)
    {
        $assignment = VehicleAssignment::findOrFail($id);

        $validated = $request->validate([
            'starting_odometer' => 'nullable|numeric|min:0',
            'assigned_by_id' => 'nullable|exists:organization_staffs,id',
            'assigned_at' => 'nullable|date',
            'released_at' => 'nullable|date|after_or_equal:assigned_at',
        ]);

        $assignment->update($validated);

        return response()->json($assignment->load(['vehicle', 'driver', 'assignedBy']));
    }

    /**
     * Delete vehicle assignment
     *
     * Remove a vehicle assignment record.
     *
     * @urlParam id int required Assignment ID. Example: 1
     *
     * @response 204
     */
    public function destroy($id)
    {
        $assignment = VehicleAssignment::findOrFail($id);
        $assignment->delete();

        return response()->json(null, 204);
    }
}
