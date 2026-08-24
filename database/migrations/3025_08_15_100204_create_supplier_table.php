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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string("supplier_name");
            $table->string("location");
            $table->string("contact_person_name")->nullable();
            $table->string("contact_person_email")->nullable();
            $table->string("contact_person_phone")->nullable();
            $table->timestamps();
        });

        // Fuel suppliers
        Schema::create('fuel_supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            $table->foreignId("fuel_id")->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Spare part suppliers
        Schema::create('spare_part_supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            $table->foreignId("spare_part_id")->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Maintenance providers
        Schema::create('maintenance_provider_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            $table->foreignId("maintenance_id")->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('fuel_supplier_details');
        Schema::dropIfExists('spare_part_supplier_details');
        Schema::dropIfExists('maintenance_provider_details');
    }
};
