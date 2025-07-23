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
            $table->increments('assignment_id');
            $table->integer('device_id')->unsigned()->notNull();
            $table->smallInteger('user_id')->unsigned()->notNull();
            $table->tinyInteger('branch_id')->unsigned()->notNull();
            $table->date('assigned_date')->notNull();
            $table->date('returned_date')->nullable();
            $table->enum('status', ['Digunakan', 'Tidak Digunakan', 'Cadangan'])->notNull();
            $table->text('notes')->nullable();
            $table->datetime('created_at')->notNull();
            $table->string('created_by', 8)->notNull();
            $table->datetime('updated_at')->nullable();
            $table->string('updated_by', 8)->nullable();
            
            $table->foreign('device_id')->references('device_id')->on('devices')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branch')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('device_id');
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
