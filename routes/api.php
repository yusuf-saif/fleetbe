<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\MaintenanceController;
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
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::prefix('driver')->group(function () {
        Route::middleware('auth:sanctum')->post('/register', [DriverAuthController::class, 'register']);
        Route::post('/login', [DriverAuthController::class, 'login']);
        Route::post('/change-password', [DriverAuthController::class, 'changePassword']);
        Route::post('/forgot-password', [DriverAuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [DriverAuthController::class, 'resetPassword']);
        // Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [DriverAuthController::class, 'logout']);
            Route::get("/", [DriverController::class, "index"]);
            Route::get("/{id}", [DriverController::class, "show"]);
            Route::delete("/{id}", [DriverController::class, "destroy"]);
            Route::put("/{id}", [DriverController::class, "update"]);
        // });
    });

    Route::middleware('auth:sanctum')->apiResource('vehicles', VehicleController::class);
    Route::middleware('auth:sanctum')->apiResource('fuel', FuelController::class);
    Route::middleware('auth:sanctum')->apiResource('maintenance', MaintenanceController::class);
    Route::middleware('auth:sanctum')->apiResource('sparepart', SparePartController::class);
    Route::middleware('auth:sanctum')->apiResource('suppliers', SupplierController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('vehicle-assignments/current', [VehicleAssignmentController::class, 'currentAssignments']);
        Route::get('vehicle-assignments/driver/{driver_id}', [VehicleAssignmentController::class, 'assignmentsByDriver']);
        Route::get('vehicle-assignments/vehicle/{vehicle_id}', [VehicleAssignmentController::class, 'assignmentsByVehicle']);
        Route::post('vehicle-assignments/{assignment}/release', [VehicleAssignmentController::class, 'release']);
    });
    Route::middleware('auth:sanctum')->apiResource('vehicle-assignments', VehicleAssignmentController::class);


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('requests', [RequestsController::class, 'index']);
        Route::post('requests', [RequestsController::class, 'store']);
        Route::get('requests/{id}', [RequestsController::class, 'show']);
        Route::post('requests/{id}/approve', [RequestsController::class, 'approve']);
        Route::post('requests/{id}/reject', [RequestsController::class, 'reject']);
    });
});


Route::fallback(function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
    ], 404);
});
