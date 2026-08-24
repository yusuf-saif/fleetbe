<?php

namespace App\Http\Controllers;

use App\Models\Requests as ResourceRequest;
use Illuminate\Http\Request;

/**
 * @group Resource & Maintenance Requests
 *
 * APIs for managing fuel, spare part, and maintenance requests
 *
 * @authenticated
 */
class RequestsController extends Controller
{
    /**
     * List requests
     *
     * Returns all requests, optionally filtered by vehicle, driver, type, or status.
     *
     * @queryParam vehicle_id int Filter by vehicle ID. Example: 5
     * @queryParam driver_id int Filter by driver ID. Example: 2
     * @queryParam requestable_type string Filter by type: fuel, spare_part, maintenance. Example: "fuel"
     * @queryParam status string Filter by request status. Example: "pending"
     * @queryParam current bool Filter only current requests. Example: true
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "vehicle_id": 5,
     *       "driver_id": 2,
     *       "approved_by_id": 1,
     *       "checked_by_id": null,
     *       "previous_id": null,
     *       "current_request": true,
     *       "requestable_type": "fuel",
     *       "requestable_id": 10,
     *       "quantity_requested": "100.00",
     *       "quantity_approved": "80.00",
     *       "vehicle_odometer": "1200.50",
     *       "current_fuel_level": "20.50",
     *       "maintenance_type": null,
     *       "description": null,
     *       "with_sparepart": false,
     *       "spare_part_id": null,
     *       "other_sparepart": null,
     *       "status": "approved",
     *       "created_at": "2025-09-11T08:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = ResourceRequest::query();

        // 🔎 Filters
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('requestable_type')) {
            $query->where('requestable_type', $request->requestable_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('current')) {
            $query->where('current_request', filter_var($request->current, FILTER_VALIDATE_BOOLEAN));
        }

        $requests = $query
            ->with(['requestable', 'sparePart']) // eager load spare part if present
            ->paginate($request->get('per_page', 20));

        return response()->json($requests);
    }

    /**
     * Create a new request
     *
     * Create a fuel, spare part, or maintenance request.
     *
     * @bodyParam vehicle_id int required Vehicle ID. Example: 5
     * @bodyParam driver_id int required Driver ID. Example: 2
     * @bodyParam requestable_type string required Type of request: fuel, spare_part, maintenance. Example: "fuel"
     * @bodyParam requestable_id int required ID of the requested resource. Example: 10
     * @bodyParam quantity_requested number nullable Quantity requested (fuel/spare part). Example: 100.50
     * @bodyParam vehicle_odometer number nullable Vehicle odometer (fuel requests). Example: 1200.50
     * @bodyParam current_fuel_level number nullable Current fuel level (fuel requests). Example: 20.50
     * @bodyParam maintenance_type string nullable Maintenance type: preventive, corrective, predictive. Example: "preventive"
     * @bodyParam description string nullable Maintenance description. Example: "Engine check"
     * @bodyParam with_sparepart boolean required Flag if maintenance requires a spare part. Example: true
     * @bodyParam spare_part_id int nullable ID of spare part if it exists. Example: 3
     * @bodyParam other_sparepart string nullable Custom spare part name if not in database. Example: "Custom fan belt"
     *
     * @response 201 {
     *   "id": 1,
     *   "vehicle_id": 5,
     *   "driver_id": 2,
     *   "requestable_type": "maintenance",
     *   "requestable_id": 10,
     *   "quantity_requested": null,
     *   "with_sparepart": true,
     *   "spare_part_id": 3,
     *   "other_sparepart": null,
     *   "status": "pending",
     *   "current_request": true
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'       => 'required|exists:vehicles,id',
            'driver_id'        => 'required|exists:drivers,id',
            'requestable_type' => 'required|string',
            'requestable_id'   => 'required|integer',
            'quantity_requested' => 'nullable|numeric|min:0',
            'vehicle_odometer'   => 'nullable|numeric|min:0',
            'current_fuel_level' => 'nullable|numeric|min:0',
            'maintenance_type'   => 'nullable|string',
            'description'        => 'nullable|string',
            'with_sparepart'     => 'required|boolean',
            'spare_part_id'      => 'nullable|required_if:with_sparepart,true|exists:spare_parts,id',
            'other_sparepart'    => 'nullable|required_if:spare_part_id,null|string|max:255',
        ]);

        $validated['requestable_type'] = strtolower(trim($validated['requestable_type']));

        $requestModel = ResourceRequest::create($validated);

        return response()->json($requestModel->load(['requestable', 'sparePart']), 201);
    }

    /**
     * Approve a request
     *
     * Set status to approved and optionally set quantity approved.
     *
     * @urlParam id int required Request ID. Example: 1
     * @bodyParam quantity_approved number nullable Quantity approved. Example: 80.00
     *
     * @response 200 {
     *   "id": 1,
     *   "status": "approved",
     *   "quantity_approved": "80.00"
     * }
     */
    public function approve(Request $request, $id)
    {
        $requestModel = ResourceRequest::findOrFail($id);

        $validated = $request->validate([
            'quantity_approved' => 'nullable|numeric|min:0',
        ]);

        $requestModel->update(array_merge($validated, ['status' => 'approved']));

        return response()->json($requestModel);
    }

    /**
     * Reject a request
     *
     * Set status to rejected.
     *
     * @urlParam id int required Request ID. Example: 1
     *
     * @bodyParam reason string nullable Reason for rejection. Example: "Insufficient stock"
     *
     * @response 200 {
     *   "id": 1,
     *   "status": "rejected"
     * }
     */
    public function reject(Request $request, $id)
    {
        $requestModel = ResourceRequest::findOrFail($id);

        if ($request->filled('reason')) {
            $requestModel->description = $request->reason;
        }

        $requestModel->status = 'rejected';
        $requestModel->save();

        return response()->json($requestModel);
    }

    /**
     * Show a request
     *
     * @urlParam id int required Request ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "vehicle_id": 5,
     *   "driver_id": 2,
     *   "requestable_type": "fuel",
     *   "requestable_id": 10,
     *   "quantity_requested": "100.50",
     *   "with_sparepart": false,
     *   "status": "pending",
     *   "current_request": true
     * }
     */
    public function show($id)
    {
        $requestModel = ResourceRequest::with(['requestable', 'sparePart'])->findOrFail($id);
        return response()->json($requestModel);
    }
}
