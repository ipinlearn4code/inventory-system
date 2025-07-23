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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->integer('changed_fields');
            $table->enum('action_type', ['CREATE', 'UPDATE', 'DELETE']);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('user_affected', 8)->nullable();
            $table->datetime('created_at');
            $table->string('created_by', 8);
            $table->index('changed_fields');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
