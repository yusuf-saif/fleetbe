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
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['allergies', 'medical_challenge']);

            // Add new JSON columns
            $table->json('allergies')->nullable()->after('genotype');
            $table->json('medical_challenge')->nullable()->after('allergies');
            $table->json('eye_condition')->nullable()->after('medical_challenge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Rollback JSON fields
            $table->dropColumn(['allergies', 'medical_challenge', 'eye_condition']);

            // Restore old structure
            $table->string('allergies')->nullable();
            $table->string('medical_challenge')->nullable();
        });
    }
};
