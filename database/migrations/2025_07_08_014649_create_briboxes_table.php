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
        Schema::create('briboxes', function (Blueprint $table) {
            $table->string('bribox_id', 2)->primary();
            $table->string('type', 25);
            $table->string('category', 25);
            $table->index('bribox_id');
            $table->index('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('briboxes');
    }
};
