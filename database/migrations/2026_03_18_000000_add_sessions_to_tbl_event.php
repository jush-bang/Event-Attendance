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
        Schema::table('tbl_event', function (Blueprint $table) {
            $table->unsignedInteger('sessions')->default(1)->after('e_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_event', function (Blueprint $table) {
            $table->dropColumn('sessions');
        });
    }
};
