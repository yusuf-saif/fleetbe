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
        Schema::create('fuels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('fuel_type', ['petrol', 'diesel', 'gas'])->index();
            $table->decimal('reserve_level', 8, 2)->default(0.00);
            $table->string('unit', 10)->default('l');
            $table->decimal("quantity_allocated", 8, 2)->default(0.00)->nullable();
            $table->decimal("quantity_remaining", 8, 2)->default(0.00)->nullable();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('size')->nullable();
            $table->mediumText('description')->nullable();
            $table->unsignedInteger('reserve_quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->unsignedInteger("quantity_allocated")->default(0);
            $table->unsignedInteger("quantity_remaining")->default(0);
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->index();
            $table->string('frequency')->nullable();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Schema::create("fuel_storages", function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('fuel_id')->constrained()->cascadeOnDelete();
        //     $table->string('title');
        //     $table->decimal('total_quantity', 8, 2)->default(0.00);
        //     $table->timestamps();
        // });

        // Schema::create("spare_part_storages", function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('spare_part_id')->constrained()->cascadeOnDelete();
        //     $table->string('title');
        //     $table->unsignedInteger('total_quantity')->default(0);
        //     $table->timestamps();
        // });

        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->morphs('storable'); // storable_id, storable_type
            $table->decimal('total_quantity', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storages');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('fuels');
        // Schema::dropIfExists('fuel_storages');
        // Schema::dropIfExists('spare_part_storages');
    }
};
