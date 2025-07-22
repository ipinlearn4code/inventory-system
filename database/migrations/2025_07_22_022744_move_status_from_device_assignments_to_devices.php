<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to devices table
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('status', ['Digunakan', 'Tidak Digunakan', 'Cadangan'])->default('Tidak Digunakan')->after('condition');
        });

        // Migrate existing status data from device_assignments to devices
        DB::statement('
            UPDATE devices d 
            JOIN device_assignments da ON d.device_id = da.device_id 
            SET d.status = da.status 
            WHERE da.returned_date IS NULL
        ');

        // Remove status column from device_assignments table
        Schema::table('device_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add status column back to device_assignments table
        Schema::table('device_assignments', function (Blueprint $table) {
            $table->enum('status', ['Digunakan', 'Tidak Digunakan', 'Cadangan'])->notNull()->after('returned_date');
        });

        // Migrate status data back from devices to device_assignments
        DB::statement('
            UPDATE device_assignments da 
            JOIN devices d ON da.device_id = d.device_id 
            SET da.status = d.status 
            WHERE da.returned_date IS NULL
        ');

        // Remove status column from devices table
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
