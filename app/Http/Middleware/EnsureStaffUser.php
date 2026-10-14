<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureStaffUser
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated actor is an active corporate staff member (User),
     * preventing drivers or suspended staff accounts from accessing fleet management operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Must be authenticated
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Must be a corporate staff User instance, not a Driver
        if (! ($user instanceof User)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: This resource requires corporate staff privileges.',
            ], 403);
        }

        // Check if staff profile exists and is suspended
        if ($user->organizationStaff && strtolower(trim($user->organizationStaff->staff_status)) === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Your staff account is currently suspended.',
            ], 403);
        }

        return $next($request);
    }
}

