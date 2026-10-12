<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * @group Organizations
 *
 * APIs for managing tenant organizations
 */
class OrganizationController extends Controller
{
    /**
     * List all organizations
     *
     * Retrieve a list of all registered organizations.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "organization_name": "Finecore Ltd",
     *       "created_at": "2025-09-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $organizations = Organization::all();

        return response()->json([
            'success' => true,
            'data'    => $organizations,
        ]);
    }

    /**
     * Create an organization
     *
     * Create a new organization in the system.
     *
     * @bodyParam organization_name string required The legal/trade name of the organization. Example: Finecore Ltd
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Organization created successfully.",
     *   "data": {
     *     "id": 1,
     *     "organization_name": "Finecore Ltd"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
        ]);

        $organization = Organization::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Organization created successfully.',
            'data'    => $organization,
        ], 201);
    }

    /**
     * Get an organization
     *
     * View details of a specific organization.
     *
     * @urlParam id int required The organization ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "organization_name": "Finecore Ltd"
     *   }
     * }
     */
    public function show($id)
    {
        $organization = Organization::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $organization,
        ]);
    }
}

