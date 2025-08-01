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
        // Use database-agnostic approach for SQLite compatibility
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE devices 
                SET status = (
                    SELECT da.status 
                    FROM device_assignments da 
                    WHERE da.device_id = devices.device_id 
                    AND da.returned_date IS NULL 
                    LIMIT 1
                ) 
                WHERE EXISTS (
                    SELECT 1 FROM device_assignments da 
                    WHERE da.device_id = devices.device_id 
                    AND da.returned_date IS NULL
                )
            ');
        } else {
            DB::statement('
                UPDATE devices d 
                JOIN device_assignments da ON d.device_id = da.device_id 
                SET d.status = da.status 
                WHERE da.returned_date IS NULL
            ');
        }

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
        // Use database-agnostic approach for SQLite compatibility
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE device_assignments 
                SET status = (
                    SELECT d.status 
                    FROM devices d 
                    WHERE d.device_id = device_assignments.device_id 
                    LIMIT 1
                ) 
                WHERE returned_date IS NULL 
                AND EXISTS (
                    SELECT 1 FROM devices d 
                    WHERE d.device_id = device_assignments.device_id
                )
            ');
        } else {
            DB::statement('
                UPDATE device_assignments da 
                JOIN devices d ON da.device_id = d.device_id 
                SET da.status = d.status 
                WHERE da.returned_date IS NULL
            ');
        }

        // Remove status column from devices table
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
