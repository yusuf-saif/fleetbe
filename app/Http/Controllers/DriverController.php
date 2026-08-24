<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * @group Drivers
 *
 * Driver management endpoints (includes relationships: organization, vehicles)
 */
class DriverController extends Controller
{
    /**
     * List drivers
     *
     * Paginated list of drivers. Each driver includes `organization` and `vehicles`.
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Results per page. Example: 10
     *
     * @response 200 {
     *  "data": [
     *      {
     *          "id": 1,
     *          "name": "John Doe",
     *          "email": "john@example.com",
     *          "organization": { "id": 1, "name": "Org Ltd" },
     *          "vehicles": [
     *              { "id": 2, "name": "Toyota Hiace", "plate_number": "ABC123" }
     *          ]
     *      }
     *  ],
     *  "links": {}, "meta": {}
     * }
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $drivers = Drivers::paginate($perPage);
        return response()->json($drivers);
    }

    /**
     * Get a driver
     *
     * Returns a driver with `organization` and `vehicles`.
     *
     * @urlParam id integer required The ID of the driver. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "organization": { "id": 1, "name": "Org Ltd" },
     *   "vehicles": [{ "id": 2, "name": "Toyota Hiace" }]
     * }
     */
    public function show($id)
    {
        $driver = Drivers::findOrFail($id);
        return response()->json($driver);
    }

    /**
     * Update a driver
     *
     * Update driver fields. Password is optional; when present it will be hashed.
     *
     * @urlParam id integer required The ID of the driver. Example: 1
     * @bodyParam name string The driver's name.
     * @bodyParam email string The driver's email.
     * @bodyParam phone_number string The driver's phone number.
     * @bodyParam password string The new password (optional).
     * @bodyParam driver_license string Driver license number.
     * @bodyParam license_expiry_date date License expiry date (YYYY-MM-DD).
     * @bodyParam next_kin_name string Next of kin name.
     * // (other fields may be provided; see model for full list)
     *
     * @response 200 {
     *   "message": "Driver updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Name",
     *     "organization": { "id": 1, "name": "Org Ltd" },
     *     "vehicles": []
     *   }
     * }
     */
    public function update(Request $request, $id)
    {
        $driver = Drivers::findOrFail($id);

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('drivers', 'email')->ignore($driver->id)],
            'phone_number' => ['sometimes', 'string', Rule::unique('drivers', 'phone_number')->ignore($driver->id)],
            'password' => 'nullable|string|min:8',
            'next_kin_name' => 'sometimes|nullable|string|max:255',
            'next_kin_relationship' => 'sometimes|nullable|string|max:255',
            'next_kin_phone' => 'sometimes|nullable|string|max:20',
            'next_kin_email' => 'sometimes|nullable|email',
            'next_kin_residential_address' => 'sometimes|nullable|string',
            'blood_group' => ['sometimes', 'nullable', Rule::in(["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"])],
            'genotype' => 'sometimes|nullable|string',
            // 'allergies' => 'sometimes|nullable|string',
            // 'medical_challenge' => 'sometimes|nullable|string',
            'allergies'         => 'sometimes|nullable|array',
            'allergies.*'       => 'sometimes|string|max:255',

            'medical_challenge' => 'sometimes|nullable|array',
            'medical_challenge.*' => 'sometimes|string|max:255',

            'eye_condition'     => 'sometimes|nullable|array',
            'eye_condition.*'   => 'sometimes|string|max:255',
            'residential_address' => 'sometimes|nullable|string',
            'home_address' => 'sometimes|nullable|string',
            'state' => 'sometimes|nullable|string',
            'lga' => 'sometimes|nullable|string',
            'town' => 'sometimes|nullable|string',
            'nationality' => 'sometimes|nullable|string',
            'state_of_origin' => 'sometimes|nullable|string',
            'lga_of_origin' => 'sometimes|nullable|string',
            'town_of_origin' => 'sometimes|nullable|string',
            'date_of_birth' => 'sometimes|nullable|date',
            'driver_license' => 'sometimes|nullable|string',
            'license_expiry_date' => 'sometimes|nullable|date',
            'must_change_password' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        if (array_key_exists('password', $validated) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Make sure we don't accidentally set password to null
            unset($validated['password']);
        }

        $driver->update($validated);

        return response()->json([
            'message' => 'Driver updated successfully',
            'data' => $driver->load(['organization']),
        ]);
    }

    /**
     * Delete a driver
     *
     * @urlParam id integer required The ID of the driver. Example: 1
     *
     * @response 200 {
     *   "message": "Driver deleted successfully"
     * }
     */
    public function destroy($id)
    {
        $driver = Drivers::findOrFail($id);
        $driver->delete();

        return response()->json(['message' => 'Driver deleted successfully']);
    }

    /**
     * Request password reset (PIN)
     *
     * Generates a PIN and sets expiry (15 mins). TODO: send via email/SMS.
     *
     * @bodyParam email string required The driver's email. Example: johndoe@example.com
     */
    public function requestPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:drivers,email']);

        $driver = Drivers::where('email', $request->email)->firstOrFail();
        $pin = rand(100000, 999999);

        $driver->update([
            'password_reset_pin' => $pin,
            'password_reset_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // DO NOT return PIN in production. For dev/testing you may return it or log it.
        return response()->json(['message' => 'Reset PIN generated (send via email/SMS)', 'pin' => $pin]);
    }

    /**
     * Reset password using PIN
     *
     * @bodyParam email string required The driver's email.
     * @bodyParam pin integer required The reset PIN.
     * @bodyParam password string required The new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:drivers,email',
            'pin' => 'required|integer',
            'password' => 'required|string|min:8',
        ]);

        $driver = Drivers::where('email', $request->email)
            ->where('password_reset_pin', $request->pin)
            ->first();

        if (!$driver || Carbon::now()->greaterThan($driver->password_reset_expires_at)) {
            return response()->json(['message' => 'Invalid or expired PIN'], 422);
        }

        $driver->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_reset_pin' => null,
            'password_reset_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }
}
