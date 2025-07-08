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
        Schema::create('device_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('device_id');
            $table->string('pn', 8)->nullable();
            $table->string('branch_code', 8);
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->enum('status', ['Digunakan', 'Tidak Digunakan', 'Cadangan']);
            $table->text('notes')->nullable();
            $table->datetime('created_at');
            $table->string('created_by', 7);
            $table->datetime('updated_at')->nullable();
            $table->string('updated_by', 7)->nullable();
            $table->foreign('device_id')->references('device_id')->on('devices')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('pn')->references('pn')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('branch_code')->references('branch_code')->on('branches')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->index('device_id');
            $table->index('pn');
            $table->index('assigned_date');
            $table->unique(['device_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_assignments');
    }
};
