<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

/**
 * @group Suppliers
 *
 * APIs for managing suppliers
 *
 * @authenticated
 */
class SupplierController extends Controller
{
    /**
     * List suppliers
     *
     * Returns a list of suppliers. Use query parameters to eager load category details.
     *
     * @queryParam with_fuel boolean Whether to include fuel details. Example: 1
     * @queryParam with_spare_parts boolean Whether to include spare parts details. Example: 1
     * @queryParam with_maintenance boolean Whether to include maintenance details. Example: 1
     *
     * @response 200 {
     *  "data": [
     *    {
     *      "id": 1,
     *      "supplier_name": "Acme Supplies",
     *      "location": "Lagos",
     *      "contact_person_name": "Jane Doe",
     *      "contact_person_email": "jane@example.com",
     *      "contact_person_phone": "+2348012345678",
     *      "created_at": "2025-09-09T12:00:00.000000Z",
     *      "updated_at": "2025-09-09T12:00:00.000000Z",
     *      "fuelDetails": [
     *          //optional when with_fuel=1
     *      ]
     *    }
     *  ]
     * }
     */
    public function index(Request $request)
    {
        $with = [];

        if ($request->query('with_fuel')) {
            $with[] = 'fuelDetails.fuel';
        }
        if ($request->query('with_spare_parts')) {
            $with[] = 'sparePartDetails.sparePart';
        }
        if ($request->query('with_maintenance')) {
            $with[] = 'maintenanceProviderDetails.maintenance';
        }

        $suppliers = Supplier::with($with)->get();

        return response()->json(['data' => $suppliers], 200);
    }

    /**
     * Create a supplier
     *
     * Create a new supplier and optionally link it to fuel, spare part, or maintenance.
     *
     * @bodyParam supplier_name string required The supplier's display name. Example: "Oando PLC"
     * @bodyParam location string required Supplier location. Example: "Lagos"
     * @bodyParam contact_person_name string nullable Contact person name. Example: "John Doe"
     * @bodyParam contact_person_email string nullable Contact person email. Example: "john@oando.com"
     * @bodyParam contact_person_phone string nullable Contact person phone. Example: "+2348012345678"
     * @bodyParam fuel_id int nullable Fuel ID to attach to this supplier. Example: 3
     * @bodyParam spare_part_id int nullable Spare part ID to attach to this supplier. Example: 5
     * @bodyParam maintenance_id int nullable Maintenance ID to attach to this supplier. Example: 2
     *
     * @response 201 {
     *   "id": 1,
     *   "supplier_name": "Oando PLC",
     *   "location": "Lagos",
     *   "contact_person_name": "John Doe",
     *   "contact_person_email": "john@oando.com",
     *   "contact_person_phone": "+2348012345678",
     *   "fuelDetails": [
     *     {
     *       "id": 1,
     *       "fuel_id": 3,
     *       "fuel": {
     *         "id": 3,
     *         "title": "Diesel"
     *       }
     *     }
     *   ],
     *   "sparePartDetails": [
     *     {
     *       "id": 1,
     *       "spare_part_id": 5,
     *       "sparePart": {
     *         "id": 5,
     *         "title": "Brake Pad"
     *       }
     *     }
     *   ],
     *   "maintenanceProviderDetails": [],
     *   "created_at": "2025-09-11T12:00:00.000000Z",
     *   "updated_at": "2025-09-11T12:00:00.000000Z"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'contact_person_phone' => 'nullable|string|max:50',

            'fuel_id' => 'nullable|exists:fuels,id',
            'spare_part_id' => 'nullable|exists:spare_parts,id',
            'maintenance_id' => 'nullable|exists:maintenances,id',
        ]);

        $supplier = Supplier::create($validated);

        if ($request->filled('fuel_id')) {
            $supplier->fuelDetails()->create([
                'fuel_id' => $request->fuel_id,
            ]);
        }

        if ($request->filled('spare_part_id')) {
            $supplier->sparePartDetails()->create([
                'spare_part_id' => $request->spare_part_id,
            ]);
        }

        if ($request->filled('maintenance_id')) {
            $supplier->maintenanceProviderDetails()->create([
                'maintenance_id' => $request->maintenance_id,
            ]);
        }

        return response()->json(
            $supplier->load('fuelDetails.fuel', 'sparePartDetails.sparePart', 'maintenanceProviderDetails.maintenance'),
            201
        );
    }

    /**
     * Show supplier
     *
     * Retrieve a supplier by id, with optional category details.
     *
     * @urlParam id int required Supplier id. Example: 1
     * @queryParam with_fuel boolean Whether to include fuel details. Example: 1
     * @queryParam with_spare_parts boolean Whether to include spare parts details. Example: 1
     * @queryParam with_maintenance boolean Whether to include maintenance details. Example: 1
     *
     * @response 200 {
     *  "id": 1,
     *  "supplier_name": "Acme Supplies",
     *  "location": "Ikeja",
     *  "contact_person_name": "Jane Doe",
     *  "contact_person_email": "jane@example.com",
     *  "contact_person_phone": "+2348012345678",
     *      "fuelDetails": [
     *          //optional
     *      ],
     *  "sparePartDetails": [
     *          //optional
     *      ],
     *  "maintenanceProviderDetails": [
     *          //optional
     *      ],
     *  "created_at": "2025-09-09T12:00:00.000000Z",
     *  "updated_at": "2025-09-09T12:00:00.000000Z"
     * }
     */
    public function show(Request $request, $id)
    {
        $with = [];

        if ($request->query('with_fuel')) {
            $with[] = 'fuelDetails.fuel';
        }
        if ($request->query('with_spare_parts')) {
            $with[] = 'sparePartDetails.sparePart';
        }
        if ($request->query('with_maintenance')) {
            $with[] = 'maintenanceProviderDetails.maintenance';
        }

        $supplier = Supplier::with($with)->findOrFail($id);

        return response()->json($supplier);
    }

    /**
     * Update supplier
     *
     * Update supplier information.
     *
     * @urlParam id int required Supplier id. Example: 1
     * @bodyParam supplier_name string sometimes Supplier name. Example: "Acme Supplies Updated"
     * @bodyParam location string sometimes Supplier location. Example: "Ikeja"
     *
     * @response 200 {
     *  "id": 1,
     *  "supplier_name": "Acme Supplies Updated",
     *  "location": "Ikeja",
     *  "contact_person_name": null,
     *  "contact_person_email": null,
     *  "contact_person_phone": null,
     *  "created_at": "2025-09-09T12:00:00.000000Z",
     *  "updated_at": "2025-09-10T08:00:00.000000Z"
     * }
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'supplier_name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'contact_person_phone' => 'nullable|string|max:50',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }

    /**
     * Delete supplier
     *
     * Remove a supplier from the system.
     *
     * @urlParam id int required Supplier id. Example: 1
     *
     * @response 204
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(null, 204);
    }
}
