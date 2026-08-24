<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\Request;

/**
 * @group Spare Parts
 *
 * APIs for managing spare parts
 */
class SparePartController extends Controller
{
    /**
     * List Spare Parts
     *
     * @queryParam organization_id int Filter by organization. Example: 1
     * @queryParam per_page int Results per page. Example: 20
     */
    public function index(Request $request)
    {
        $query = SparePart::query();

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        return response()->json(
            $query->paginate($request->get('per_page', 20))
        );
    }

    /**
     * Create Spare Part
     *
     * @bodyParam title string required The spare part name. Example: Brake Pad
     * @bodyParam size string The size. Example: Medium
     * @bodyParam description string The description.
     * @bodyParam reserve_quantity int Default reserve stock. Example: 10
     * @bodyParam unit string Unit of measurement. Example: pcs
     * @bodyParam quantity_allocated int Allocated stock. Example: 50
     * @bodyParam quantity_remaining int Remaining stock. Example: 20
     * @bodyParam organization_id int required The organization. Example: 1
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'size' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reserve_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'quantity_allocated' => 'nullable|integer|min:0',
            'quantity_remaining' => 'nullable|integer|min:0',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $sparePart = SparePart::create($validated);

        return response()->json($sparePart, 201);
    }

    /**
     * Show Spare Part
     *
     * @urlParam id int required Spare part ID. Example: 1
     */
    public function show($id)
    {
        $sparePart = SparePart::findOrFail($id);
        return response()->json($sparePart);
    }

    /**
     * Update Spare Part
     *
     * @urlParam id int required Spare part ID. Example: 1
     */
    public function update(Request $request, $id)
    {
        $sparePart = SparePart::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'size' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reserve_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'quantity_allocated' => 'nullable|integer|min:0',
            'quantity_remaining' => 'nullable|integer|min:0',
            'organization_id' => 'sometimes|required|exists:organizations,id',
        ]);

        $sparePart->update($validated);

        return response()->json($sparePart);
    }

    /**
     * Delete Spare Part
     *
     * @urlParam id int required Spare part ID. Example: 1
     */
    public function destroy($id)
    {
        $sparePart = SparePart::findOrFail($id);
        $sparePart->delete();

        return response()->json(['message' => 'Spare part deleted successfully']);
    }
}
