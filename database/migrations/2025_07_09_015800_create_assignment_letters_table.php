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
        Schema::create('assignment_letters', function (Blueprint $table) {
            $table->increments('letter_id');
            $table->integer('assignment_id')->unsigned()->notNull();
            $table->enum('letter_type', ['Keluar', 'Masuk'])->notNull();
            $table->string('letter_number', 50)->unique()->notNull();
            $table->date('letter_date')->notNull();
            $table->smallInteger('approver_id')->unsigned()->notNull();
            $table->string('file_path', 255)->nullable();
            $table->datetime('created_at')->notNull();
            $table->string('created_by', 7)->notNull();
            $table->datetime('updated_at')->nullable();
            $table->string('updated_by', 7)->nullable();
            
            $table->foreign('assignment_id')->references('assignment_id')->on('device_assignments')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('approver_id')->references('user_id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');
            
            $table->index('assignment_id');
            $table->index('letter_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_letters');
    }
};
