<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("requests", function (Blueprint $table) {
            $table->id();

            // Who & what
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained("organization_staffs")->cascadeOnDelete();
            $table->foreignId('checked_by_id')->nullable()->constrained("organization_staffs")->cascadeOnDelete();

            // Self-reference for history (re-requests)
            $table->foreignId('previous_id')->nullable()->constrained("requests")->cascadeOnDelete();
            $table->boolean("current_request")->default(true);

            // Polymorphic relation (Fuel, SparePart, MaintenanceConfig, etc.)
            $table->string("requestable_type");
            $table->unsignedBigInteger("requestable_id");

            // Quantities (optional depending on type)
            $table->decimal("quantity_requested", 10, 2)->nullable();
            $table->decimal("quantity_approved", 10, 2)->nullable();

            // Odometer / fuel level (relevant for fuel)
            $table->decimal("vehicle_odometer", 10, 2)->nullable();
            $table->decimal("current_fuel_level", 10, 2)->nullable();

            // Maintenance-only fields
            $table->string("maintenance_type")->nullable(); // preventive, corrective, predictive
            $table->mediumText("description")->nullable(); // issue details

            // Status
            $table->enum("status", [
                "pending",
                "approved",
                "rejected",
                "in_progress",
                "completed"
            ])->default("pending");

            $table->timestamps();

            // Indexes
            $table->index(["requestable_type", "requestable_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("requests");
    }
};
