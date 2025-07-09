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
        Schema::create('branch', function (Blueprint $table) {
            $table->tinyIncrements('branch_id');
            $table->string('branch_code', 8)->unique()->notNull();
            $table->string('unit_name', 50)->notNull();
            $table->tinyInteger('main_branch_id')->unsigned()->notNull();
            
            $table->foreign('main_branch_id')->references('main_branch_id')->on('main_branch')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('unit_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
