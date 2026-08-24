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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->enum("type", ["Car", "Van", "Truck", "Bus"])->default("Car");
            $table->string("plate_number")->unique();
            $table->string("chasis_number")->unique();
            $table->string("manufacturer");
            $table->enum("condition", ["In Good Condition", "Flagged for Repair", "Damaged"])->default("In Good Condition");
            $table->enum("status", ["Assigned", "Unassigned", "Under Maintenance", "Parked"])->default("Unassigned");
            $table->decimal('fuel_capacity', 8, 2)->default(0.00);

            $table->foreignId("organization_id")->constrained()->cascadeOnDelete();
            $table->foreignId("driver_id")->nullable()->constrained("drivers")->nullOnDelete();
            $table->foreignId("user_assigned_id")->nullable()->constrained("organization_staffs")->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
