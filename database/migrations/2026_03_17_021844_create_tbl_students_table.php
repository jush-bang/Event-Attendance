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
        if (!Schema::hasTable('tbl_students')) {
            Schema::create('tbl_students', function (Blueprint $table) {
                $table->string('snumber', 50)->primary();
                $table->unsignedInteger('e_id');
                $table->string('name');
                $table->string('section');
                $table->string('rfid')->nullable();
                $table->timestamps();
                
                $table->foreign('e_id')->references('e_id')->on('tbl_event')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_students');
    }
};
