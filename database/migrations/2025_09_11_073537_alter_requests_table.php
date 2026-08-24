<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['requestable_type', 'requestable_id']);

            // Add polymorphic columns
            $table->morphs('requestable');

            // Add index
            $table->index("status");
            $table->index("current_request");
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Drop morphs
            $table->dropMorphs('requestable');

            // Restore original columns
            $table->string('requestable_type');
            $table->unsignedBigInteger('requestable_id');

            // Re-add index
            $table->index(['requestable_type', 'requestable_id']);
        });
    }
};
