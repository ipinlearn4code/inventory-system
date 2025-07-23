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
        // Fix devices table user reference fields from varchar(7) to varchar(8)
        Schema::table('devices', function (Blueprint $table) {
            $table->string('created_by', 8)->change();
            $table->string('updated_by', 8)->nullable()->change();
        });

        // Fix device_assignments table user reference fields from varchar(7) to varchar(8)
        Schema::table('device_assignments', function (Blueprint $table) {
            $table->string('created_by', 8)->change();
            $table->string('updated_by', 8)->nullable()->change();
        });

        // Fix assignment_letters table user reference fields from varchar(7) to varchar(8)
        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->string('created_by', 8)->change();
            $table->string('updated_by', 8)->nullable()->change();
        });

        // Check and fix inventory_logs table if it exists and has similar issues
        if (Schema::hasTable('inventory_logs')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_logs', 'created_by')) {
                    $table->string('created_by', 8)->change();
                }
                if (Schema::hasColumn('inventory_logs', 'user_affected')) {
                    $table->string('user_affected', 8)->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert devices table
        Schema::table('devices', function (Blueprint $table) {
            $table->string('created_by', 7)->change();
            $table->string('updated_by', 7)->nullable()->change();
        });

        // Revert device_assignments table
        Schema::table('device_assignments', function (Blueprint $table) {
            $table->string('created_by', 7)->change();
            $table->string('updated_by', 7)->nullable()->change();
        });

        // Revert assignment_letters table
        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->string('created_by', 7)->change();
            $table->string('updated_by', 7)->nullable()->change();
        });

        // Revert inventory_logs table if it exists
        if (Schema::hasTable('inventory_logs')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_logs', 'created_by')) {
                    $table->string('created_by', 7)->change();
                }
                if (Schema::hasColumn('inventory_logs', 'user_affected')) {
                    $table->string('user_affected', 7)->nullable()->change();
                }
            });
        }
    }
};
