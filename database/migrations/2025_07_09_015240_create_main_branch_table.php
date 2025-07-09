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
        Schema::create('main_branch', function (Blueprint $table) {
            $table->tinyIncrements('main_branch_id');
            $table->string('main_branch_code', 4)->unique()->notNull();
            $table->string('main_branch_name', 50)->notNull();
            
            $table->index('main_branch_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_branch');
    }
};
