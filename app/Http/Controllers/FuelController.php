<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use Illuminate\Http\Request;

/**
 * @group Fuels
 * @authenticated
 * APIs for managing fuels
 */
class FuelController extends Controller
{
    /**
     * Get all fuels
     *
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Petrol Reserve",
     *       "fuel_type": "petrol",
     *       "reserve_level": "5000.00",
     *       "unit": "l",
     *       "quantity_allocated": "1200.00",
     *       "quantity_remaining": "3800.00"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Fuel::with('storages')->get(),
        ]);
    }

    /**
     * Create a new fuel
     *
     * @bodyParam title string required The fuel title. Example: Petrol Reserve
     * @bodyParam fuel_type string required Type of fuel (petrol, diesel, gas). Example: petrol
     * @bodyParam reserve_level number Initial reserve level. Example: 5000.00
     * @bodyParam unit string The measurement unit. Example: l
     * @bodyParam quantity_allocated number The allocated quantity. Example: 1000.00
     * @bodyParam quantity_remaining number The remaining quantity. Example: 4000.00
     * @bodyParam organization_id int required The owning organization ID. Example: 1
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'fuel_type' => 'required|in:petrol,diesel,gas',
            'reserve_level' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'quantity_allocated' => 'nullable|numeric|min:0',
            'quantity_remaining' => 'nullable|numeric|min:0',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $fuel = Fuel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fuel created successfully.',
            'data' => $fuel,
        ], 201);
    }

    /**
     * Get a single fuel
     *
     * @urlParam id int required The ID of the fuel. Example: 1
     */
    public function show($id)
    {
        $fuel = Fuel::with('storages')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $fuel,
        ]);
    }

    /**
     * Update a fuel
     *
     * @urlParam id int required The ID of the fuel. Example: 1
     */
    public function update(Request $request, $id)
    {
        $fuel = Fuel::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'fuel_type' => 'sometimes|in:petrol,diesel,gas',
            'reserve_level' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'quantity_allocated' => 'nullable|numeric|min:0',
            'quantity_remaining' => 'nullable|numeric|min:0',
            'organization_id' => 'sometimes|exists:organizations,id',
        ]);

        $fuel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fuel updated successfully.',
            'data' => $fuel,
        ]);
    }

    /**
     * Delete a fuel
     *
     * @urlParam id int required The ID of the fuel. Example: 1
     */
    public function destroy($id)
    {
        $fuel = Fuel::findOrFail($id);
        $fuel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fuel deleted successfully.',
        ]);
    }
}
