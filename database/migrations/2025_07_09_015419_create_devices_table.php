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
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('device_id');
            $table->string('brand_name', 50)->notNull();
            $table->string('serial_number', 50)->unique()->notNull();
            $table->string('asset_code', 20)->unique()->notNull();
            $table->string('bribox_id', 2)->notNull();
            $table->enum('condition', ['Baik', 'Rusak', 'Perlu Pengecekan'])->notNull();
            $table->string('spec1', 100)->nullable();
            $table->string('spec2', 100)->nullable();
            $table->string('spec3', 100)->nullable();
            $table->string('spec4', 100)->nullable();
            $table->string('spec5', 100)->nullable();
            $table->date('dev_date')->nullable();
            $table->datetime('created_at')->notNull();
            $table->string('created_by', 7)->notNull();
            $table->datetime('updated_at')->nullable();
            $table->string('updated_by', 7)->nullable();
            
            $table->foreign('bribox_id')->references('bribox_id')->on('briboxes')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('serial_number');
            $table->index('asset_code');
            $table->index('bribox_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
