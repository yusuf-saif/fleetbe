<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * This endpoint registers a user and returns the user details with a token.
     *
     * @bodyParam name string required The name of the user. Example: Hassan Sani
     * @bodyParam email string required The email of the user. Example: hassan@example.com
     * @bodyParam password string required The password. Example: secret123
     * @bodyParam password_confirmation string required Confirm the password. Example: secret123
     *
     * @response 201 {
     *  "message": "User registered and logged in successfully",
     *  "user": {
     *      "id": 1,
     *      "name": "Hassan Sani",
     *      "email": "hassan@example.com"
     *  }
     * }
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }

    /**
     * Login a user
     *
     * This endpoint allows a user to log in and receive a Bearer token.
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
     *      "email": "hassan@example.com",
     *      "organization_staff": {
     *          "id": 5,
     *          "staff_position": "Manager",
     *          "staff_status": "Active",
     *          "staff_level": "Senior",
     *          "organization": {
     *              "id": 2,
     *              "organization_name": "Finecore Ltd"
     *          }
     *      }
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

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user()->load(['organizationStaff.organization']);
        $token = $user->createToken('auth_token')->plainTextToken; // requires Sanctum

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    /**
     * Logout the current user
     *
     * This endpoint revokes the current access token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete;

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Forgot password (send reset link)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker("users")->sendResetLink(
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

        $status = Password::broker("users")->reset(
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
