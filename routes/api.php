<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VehicleAssignmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {
    Route::get("/status", [StatusController::class, "status"]);
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::prefix('driver')->group(function () {
        Route::post('/register', [DriverAuthController::class, 'register']);
        Route::post('/login', [DriverAuthController::class, 'login']);
        Route::post('/change-password', [DriverAuthController::class, 'changePassword']);
        Route::post('/forgot-password', [DriverAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [DriverAuthController::class, 'resetPassword']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [DriverAuthController::class, 'logout']);
            Route::get("/", [DriverController::class, "index"]);
            Route::get("/{id}", [DriverController::class, "show"]);
            Route::delete("/{id}", [DriverController::class, "destroy"]);
            Route::put("/{id}", [DriverController::class, "update"]);
        });
    });

    // Self-service read access for authenticated actors (drivers & staff)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('vehicle-assignments/driver/{driver_id}', [VehicleAssignmentController::class, 'assignmentsByDriver']);
        Route::get('vehicle-assignments/current', [VehicleAssignmentController::class, 'currentAssignments']);
        Route::get('vehicle-assignments/{vehicle_assignment}', [VehicleAssignmentController::class, 'show']);
        Route::get('fuel', [FuelController::class, 'index']);
        Route::get('fuel/{id}', [FuelController::class, 'show']);
        Route::get('vehicles/{id}', [VehicleController::class, 'show']);
        Route::get('vehicles', [VehicleController::class, 'index']);

        Route::get('requests', [RequestsController::class, 'index']);
        Route::post('requests', [RequestsController::class, 'store']);
        Route::get('requests/{id}', [RequestsController::class, 'show']);

        // Management & mutation actions restricted to corporate staff
        Route::middleware('staff')->group(function () {
            Route::post('vehicles', [VehicleController::class, 'store']);
            Route::put('vehicles/{id}', [VehicleController::class, 'update']);
            Route::patch('vehicles/{id}', [VehicleController::class, 'update']);
            Route::delete('vehicles/{id}', [VehicleController::class, 'destroy']);

            Route::post('fuel', [FuelController::class, 'store']);
            Route::put('fuel/{id}', [FuelController::class, 'update']);
            Route::patch('fuel/{id}', [FuelController::class, 'update']);
            Route::delete('fuel/{id}', [FuelController::class, 'destroy']);

            Route::apiResource('maintenance', MaintenanceController::class);
            Route::apiResource('sparepart', SparePartController::class);
            Route::apiResource('suppliers', SupplierController::class);

            Route::get('vehicle-assignments/vehicle/{vehicle_id}', [VehicleAssignmentController::class, 'assignmentsByVehicle']);
            Route::post('vehicle-assignments/{assignment}/release', [VehicleAssignmentController::class, 'release']);
            Route::get('vehicle-assignments', [VehicleAssignmentController::class, 'index']);
            Route::post('vehicle-assignments', [VehicleAssignmentController::class, 'store']);
            Route::put('vehicle-assignments/{vehicle_assignment}', [VehicleAssignmentController::class, 'update']);
            Route::patch('vehicle-assignments/{vehicle_assignment}', [VehicleAssignmentController::class, 'update']);
            Route::delete('vehicle-assignments/{vehicle_assignment}', [VehicleAssignmentController::class, 'destroy']);

            Route::post('requests/{id}/approve', [RequestsController::class, 'approve']);
            Route::post('requests/{id}/reject', [RequestsController::class, 'reject']);
        });
    });
});


Route::fallback(function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
    ], 404);
});
