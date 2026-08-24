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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->string("next_kin_name");
            $table->string("next_kin_relationship");
            $table->string("next_kin_phone");
            $table->boolean('must_change_password')->default(true);
            $table->string('password_reset_pin')->nullable();
            $table->dateTime('password_reset_expires_at')->nullable();
            $table->mediumText("next_kin_residential_address");
            $table->string("next_kin_email")->nullable();
            $table->enum("blood_group", ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"])->nullable();

            $table->string("genotype")->nullable();
            $table->string("allergies")->nullable();
            $table->string("medical_challenge")->nullable();
            $table->mediumText("residential_address")->nullable();
            $table->foreignId("organization_id")->constrained()->cascadeOnDelete();
            $table->mediumText("home_address")->nullable();
            $table->string("state")->nullable();
            $table->string("lga")->nullable();
            $table->string("town")->nullable();
            $table->string("nationality")->nullable();
            $table->string("state_of_origin")->nullable();
            $table->string("lga_of_origin")->nullable();
            $table->string("town_of_origin")->nullable();
            $table->date("date_of_birth")->nullable();
            $table->string("driver_license");
            $table->date("license_expiry_date");
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
