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
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('user_id');
            $table->string('pn', 8)->unique()->notNull();
            $table->string('name', 50)->notNull();
            $table->string('department_id', 4)->notNull();
            $table->string('position', 100)->nullable();
            
            $table->foreign('department_id')->references('department_id')->on('departments')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('pn');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
