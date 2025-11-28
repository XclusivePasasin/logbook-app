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
        Schema::table('trips', function (Blueprint $table) {
            // Remove old column
            $table->dropColumn('is_round_trip');
            
            // Add new columns
            $table->boolean('is_ida')->default(false)->after('equipment_number');
            $table->boolean('is_vuelta')->default(false)->after('is_ida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['is_ida', 'is_vuelta']);
            
            // Restore old column
            $table->boolean('is_round_trip')->default(false);
        });
    }
};
