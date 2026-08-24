<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;

/**
 * @group Maintenances
 *
 * APIs for managing maintenance configurations
 */
class MaintenanceController extends Controller
{
    /**
     * List Maintenances
     *
     * @queryParam organization_id int Filter by organization. Example: 1
     * @queryParam per_page int Results per page. Example: 20
     */
    public function index(Request $request)
    {
        $query = Maintenance::query();

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        return response()->json(
            $query->paginate($request->get('per_page', 20))
        );
    }

    /**
     * Create Maintenance
     *
     * @bodyParam title string required Maintenance title. Example: Oil Change
     * @bodyParam type string required Maintenance type. Example: preventive
     * @bodyParam frequency string Maintenance frequency. Example: monthly
     * @bodyParam organization_id int required Organization ID. Example: 1
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'organization_id' => 'required|exists:organizations,id',

        ]);

        $maintenance = Maintenance::create($validated);

        return response()->json($maintenance, 201);
    }

    /**
     * Show Maintenance
     *
     * @urlParam id int required Maintenance ID. Example: 1
     */
    public function show($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        return response()->json($maintenance);
    }

    /**
     * Update Maintenance
     *
     * @urlParam id int required Maintenance ID. Example: 1
     */
    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'organization_id' => 'sometimes|required|exists:organizations,id',
        ]);

        $maintenance->update($validated);

        return response()->json($maintenance);
    }

    /**
     * Delete Maintenance
     *
     * @urlParam id int required Maintenance ID. Example: 1
     */
    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json(['message' => 'Maintenance deleted successfully']);
    }
}
