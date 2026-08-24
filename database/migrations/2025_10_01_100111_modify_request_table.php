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
        Schema::table('requests', function (Blueprint $table) {
            // Flag for spare part requirement
            $table->boolean('with_sparepart')->default(false);

            // Optional spare part reference
            $table->foreignId('spare_part_id')
                ->nullable()
                ->constrained('spare_parts')
                ->cascadeOnDelete();

            // Fallback if no spare part exists in DB
            $table->string('other_sparepart')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            //
        });
    }
};
