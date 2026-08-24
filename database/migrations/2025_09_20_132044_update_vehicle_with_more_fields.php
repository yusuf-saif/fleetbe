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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('asset_number')->nullable()->after('status');
            $table->string('vehicle_security_number')->nullable()->after('asset_number');
            $table->date('date_purchased')->nullable()->after('vehicle_security_number');
            $table->year('manufactured_year')->nullable()->after('date_purchased');
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->nullable()->after('manufactured_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'asset_number',
                'vehicle_security_number',
                'date_purchased',
                'manufactured_year',
                'fuel_type',
            ]);
        });
    }
};
