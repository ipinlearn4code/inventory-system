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
            $table->string('type', 40)->notNull();
            $table->tinyInteger('bribox_category_id')->unsigned()->notNull();
            
            $table->foreign('bribox_category_id')->references('bribox_category_id')->on('briboxes_category')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('bribox_id');
            $table->index('type');
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
