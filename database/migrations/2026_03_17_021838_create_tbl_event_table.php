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
        if (!Schema::hasTable('tbl_event')) {
            Schema::create('tbl_event', function (Blueprint $table) {
                $table->unsignedInteger('e_id')->autoIncrement()->primary();
                $table->string('e_name');
                $table->date('start_date');
                $table->date('end_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('e_location')->default('Not Specified');
                $table->string('e_status')->default('active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_event');
    }
};
