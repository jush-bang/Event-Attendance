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
        if (!Schema::hasTable('tbl_attendance')) {
            Schema::create('tbl_attendance', function (Blueprint $table) {
                $table->unsignedInteger('id')->autoIncrement()->primary();
                $table->unsignedInteger('event_id');
                $table->string('snumber', 50);
                $table->dateTime('time_in')->nullable();
                $table->dateTime('time_out')->nullable();

                // Foreign keys
                $table->foreign('event_id')->references('e_id')->on('tbl_event')->onDelete('cascade');
                $table->foreign('snumber')->references('snumber')->on('tbl_students')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_attendance');
    }
};
