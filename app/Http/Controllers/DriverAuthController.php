<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Drivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

/**
 * @group Driver Authentication
 */
class DriverAuthController extends Controller
{
    /**
     * Register a new driver
     *
     * @group Drivers
     *
     * This endpoint registers a new driver in the system.
     *
     * @bodyParam name string required The driver's full name. Example: John Doe
     * @bodyParam email string required Unique email address. Example: john@example.com
     * @bodyParam phone_number string required Unique phone number. Example: 08012345678
     * @bodyParam password string The driver's password (nullable, default is generated if empty). Example: secret123
     * @bodyParam organization_id integer required The ID of the organization the driver belongs to. Example: 1
     * @bodyParam driver_license string required The driver's license number. Example: ABC12345
     * @bodyParam license_expiry_date date required Expiry date of the driver's license. Example: 2026-05-01
     * @bodyParam next_kin_name string The next of kin's name. Example: Jane Doe
     * @bodyParam next_kin_relationship string The relationship with next of kin. Example: Sister
     * @bodyParam next_kin_phone string The next of kin's phone number. Example: 08098765432
     * @bodyParam next_kin_email string The next of kin's email address. Example: jane@example.com
     * @bodyParam next_kin_residential_address string The residential address of next of kin. Example: 10 First Avenue, Abuja
     * @bodyParam blood_group string The blood group of the driver. Example: O+
     * @bodyParam genotype string The genotype of the driver. Example: AA
     * @bodyParam allergies array A list of allergies (array of strings). Example: ["Peanuts", "Dust"]
     * @bodyParam medical_challenge array A list of medical challenges (array of strings). Example: ["Asthma", "Hypertension"]
     * @bodyParam eye_condition array A list of eye conditions (array of strings). Example: ["Short-sightedness"]
     * @bodyParam residential_address string The residential address of the driver. Example: 12 Main Street, Abuja
     * @bodyParam home_address string The home address of the driver. Example: 5 Old Road, Kano
     * @bodyParam state string The state where the driver lives. Example: Lagos
     * @bodyParam lga string The LGA where the driver lives. Example: Ikeja
     * @bodyParam town string The town where the driver lives. Example: Ojodu
     * @bodyParam nationality string The nationality of the driver. Example: Nigerian
     * @bodyParam state_of_origin string The state of origin. Example: Kaduna
     * @bodyParam lga_of_origin string The LGA of origin. Example: Zaria
     * @bodyParam town_of_origin string The town of origin. Example: Samaru
     * @bodyParam date_of_birth date The date of birth. Example: 1990-06-15
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "phone_number": "08012345678",
     *   "organization_id": 1,
     *   "driver_license": "ABC12345",
     *   "license_expiry_date": "2026-05-01",
     *   "next_kin_name": "Jane Doe",
     *   "next_kin_relationship": "Sister",
     *   "next_kin_phone": "08098765432",
     *   "next_kin_email": "jane@example.com",
     *   "next_kin_residential_address": "10 First Avenue, Abuja",
     *   "blood_group": "O+",
     *   "genotype": "AA",
     *   "allergies": ["Peanuts", "Dust"],
     *   "medical_challenge": ["Asthma", "Hypertension"],
     *   "eye_condition": ["Short-sightedness"],
     *   "residential_address": "12 Main Street, Abuja",
     *   "home_address": "5 Old Road, Kano",
     *   "state": "Lagos",
     *   "lga": "Ikeja",
     *   "town": "Ojodu",
     *   "nationality": "Nigerian",
     *   "state_of_origin": "Kaduna",
     *   "lga_of_origin": "Zaria",
     *   "town_of_origin": "Samaru",
     *   "date_of_birth": "1990-06-15",
     *   "created_at": "2025-09-20T10:00:00.000000Z",
     *   "updated_at": "2025-09-20T10:00:00.000000Z"
     * }
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:drivers',
            'phone_number'        => 'required|string|unique:drivers',
            'password'            => 'nullable',
            'organization_id'     => 'required|exists:organizations,id',
            'driver_license'      => 'required|string',
            'license_expiry_date' => 'required|date',
            'next_kin_name'       => 'nullable|string|max:255',
            'next_kin_relationship' => 'nullable|string|max:255',
            'next_kin_phone'      => 'nullable|string|max:20',
            'next_kin_email'      => 'nullable|email',
            'next_kin_residential_address' => 'nullable|string',
            'blood_group'         => 'nullable|string',
            'genotype'            => 'nullable|string',
            // 'allergies'           => 'nullable|string',
            // 'medical_challenge'   => 'nullable|string',

            'allergies'         => 'nullable|array',
            'allergies.*'       => 'string|max:255',

            'medical_challenge' => 'nullable|array',
            'medical_challenge.*' => 'string|max:255',

            'eye_condition'     => 'nullable|array',
            'eye_condition.*'   => 'string|max:255',

            'residential_address' => 'nullable|string',
            'home_address'        => 'nullable|string',
            'state'               => 'nullable|string',
            'lga'                 => 'nullable|string',
            'town'                => 'nullable|string',
            'nationality'         => 'nullable|string',
            'state_of_origin'     => 'nullable|string',
            'lga_of_origin'       => 'nullable|string',
            'town_of_origin'      => 'nullable|string',
            'date_of_birth'       => 'nullable|date',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Generate random system password
        $randomPassword = Str::random(12);
        $data = $validator->validated();
        $data['password'] = Hash::make($data['password'] ?? $randomPassword);
        // Generate random 6-digit pin for password change
        $pinCode = random_int(100000, 999999);

        $data['pin_code'] = $pinCode;
        $data['password_reset_pin'] = $pinCode;

        $driver = Drivers::create($data);

        // Send email with PIN
        Mail::raw("Welcome {$driver->name}, your PIN code is: {$pinCode}. Use it to set your new password use password. use the password {$randomPassword}", function ($message) use ($driver) {
            $message->to($driver->email)
                ->subject('Your Account PIN Code');
        });

        // Example: send SMS (you can integrate Twilio, Termii, etc.)
        // SmsService::send($driver->phone_number, "Your PIN code is: {$pinCode}");

        return response()->json([
            'message' => 'Driver registered successfully. A PIN has been sent to the email and phone.',
            'driver_id' => $driver->id,
        ], 201);
    }

    /**
     * Login a driver
     *
     * This endpoint allows a user to log in and receive a Bearer token.
     *
     * @group Driver Authentication
     *
     * @bodyParam email string required The user’s email. Example: hassan@example.com
     * @bodyParam password string required The user’s password. Example: secret123
     *
     * @response 200 {
     *  "message": "Login successful",
     *  "token": "1|ZpRzVvq4UIq2p...xyz",
     *  "user": {
     *      "id": 1,
     *      "name": "Hassan Sani",
     *      "email": "hassan@example.com"
     *  }
     * }
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('driver')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $driver = Auth::guard('driver')->user();

        // if ($driver->must_change_password) {
        //     // Generate random 6-digit pin
        //     $pin = rand(100000, 999999);

        //     $driver->update([
        //         'password_reset_pin' => $pin,
        //         'password_reset_expires_at' => now()->addMinutes(10),
        //     ]);

        //     // Send via email
        //     Mail::to($driver->email)->send(new \App\Mail\DriverPasswordResetPin($driver, $pin));

        //     // TODO: integrate SMS provider (Twilio, Termii, etc.)
        //     // SmsService::send($driver->phone_number, "Your password reset code is $pin");

        //     return response()->json([
        //         'message' => 'You must change your password before continuing. A reset PIN has been sent to your email and phone.',
        //     ], 403);
        // }

        // if ($driver->must_change_password) {
        //     return response()->json([
        //         'message' => 'You must change your password before continuing.'
        //     ], 403);
        // }


        // If you use Sanctum or Passport, issue token here.
        $token = $driver->createToken('driver_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'driver' => $driver,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete;

        return response()->json(['message' => 'Logged out']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:drivers,email',
            'pin' => 'required|digits:6',
            'new_password' => 'required|min:6|confirmed', // expects new_password + new_password_confirmation
        ]);

        $driver = Drivers::where('email', $request->email)->first();

        // Check if PIN matches
        if ($driver->password_reset_pin !== $request->pin) {
            return response()->json([
                'message' => 'Invalid or expired PIN code',
            ], 422);
        }

        // Update password
        $driver->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
            'password_reset_pin' => null, // clear the pin after use
        ]);

        return response()->json([
            'message' => 'Password changed successfully. You can now log in.',
        ], 200);
    }


    /**
     * Forgot password (send reset link)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker("drivers")->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent'])
            : response()->json(['message' => 'Unable to send reset link'], 500);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::broker("drivers")->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successful'])
            : response()->json(['message' => 'Password reset failed'], 500);
    }
}



// <?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Driver;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;
// use Illuminate\Support\Carbon;

// /**
//  * @group Driver Authentication
//  *
//  * APIs for authenticating drivers
//  */
// class DriverAuthController extends Controller
// {
//     /**
//      * Login
//      *
//      * @bodyParam email string required Driver’s email.
//      * @bodyParam password string required Driver’s password.
//      *
//      * @response scenario=success {
//      *   "token": "1|xyz123...",
//      *   "must_change_password": true
//      * }
//      */
//     public function login(Request $request)
//     {
//         $credentials = $request->validate([
//             'email' => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $driver = Driver::where('email', $credentials['email'])->first();

//         if (!$driver || !Hash::check($credentials['password'], $driver->password)) {
//             return response()->json(['message' => 'Invalid credentials'], 401);
//         }

//         $token = $driver->createToken('driver-token')->plainTextToken;

//         return response()->json([
//             'token' => $token,
//             'must_change_password' => $driver->must_change_password,
//         ]);
//     }

//     /**
//      * Logout
//      *
//      * @header Authorization Bearer {token}
//      */
//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()->delete();

//         return response()->json(['message' => 'Logged out successfully']);
//     }

//     /**
//      * Change password
//      *
//      * Used when a driver must change password on first login, or voluntarily.
//      *
//      * @header Authorization Bearer {token}
//      * @bodyParam old_password string required Current password.
//      * @bodyParam new_password string required New password.
//      */
//     public function changePassword(Request $request)
//     {
//         $request->validate([
//             'old_password' => 'required|string',
//             'new_password' => 'required|string|min:8',
//         ]);

//         $driver = $request->user();

//         if (!Hash::check($request->old_password, $driver->password)) {
//             return response()->json(['message' => 'Old password is incorrect'], 422);
//         }

//         $driver->password = Hash::make($request->new_password);
//         $driver->must_change_password = false;
//         $driver->save();

//         return response()->json(['message' => 'Password updated successfully']);
//     }

//     /**
//      * Request password reset
//      *
//      * Generates a PIN and sets expiry.
//      *
//      * @bodyParam email string required Driver’s registered email.
//      */
//     public function requestPasswordReset(Request $request)
//     {
//         $request->validate(['email' => 'required|email']);

//         $driver = Driver::where('email', $request->email)->first();

//         if (!$driver) {
//             return response()->json(['message' => 'Driver not found'], 404);
//         }

//         $pin = rand(100000, 999999);
//         $driver->password_reset_pin = $pin;
//         $driver->password_reset_expires_at = Carbon::now()->addMinutes(15);
//         $driver->save();

//         // TODO: send PIN via email/SMS
//         return response()->json([
//             'message' => 'Password reset PIN sent',
//             'pin' => $pin, // remove in production, only for testing now
//         ]);
//     }

//     /**
//      * Reset password using PIN
//      *
//      * @bodyParam email string required Driver’s email.
//      * @bodyParam pin int required The reset PIN.
//      * @bodyParam new_password string required New password.
//      */
//     public function resetPassword(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|email',
//             'pin' => 'required|integer',
//             'new_password' => 'required|string|min:8',
//         ]);

//         $driver = Driver::where('email', $request->email)->first();

//         if (
//             !$driver ||
//             $driver->password_reset_pin != $request->pin ||
//             !$driver->password_reset_expires_at ||
//             Carbon::now()->greaterThan($driver->password_reset_expires_at)
//         ) {
//             return response()->json(['message' => 'Invalid or expired PIN'], 422);
//         }

//         $driver->password = Hash::make($request->new_password);
//         $driver->password_reset_pin = null;
//         $driver->password_reset_expires_at = null;
//         $driver->must_change_password = false;
//         $driver->save();

//         return response()->json(['message' => 'Password reset successful']);
//     }
// }
