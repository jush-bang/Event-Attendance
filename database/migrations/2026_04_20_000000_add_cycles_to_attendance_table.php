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
        Schema::table('tbl_attendance', function (Blueprint $table) {
            // Add new columns for cycle tracking
            $table->json('cycles_data')->nullable()->after('time_out')->comment('JSON array of attendance cycles: {time_in, break_time, time_out, duration_mins}');
            $table->enum('status', ['present', 'left_session', 'absent'])->default('absent')->after('cycles_data')->comment('Current attendance status: present, left_session, or absent');
            $table->unsignedInteger('total_duration_minutes')->default(0)->after('status')->comment('Total minutes present (sum of all cycles)');
            $table->dateTime('last_scan_time')->nullable()->after('total_duration_minutes')->comment('Last time scanned (for 30-second cooldown)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_attendance', function (Blueprint $table) {
            $table->dropColumn(['cycles_data', 'status', 'total_duration_minutes', 'last_scan_time']);
        });
    }
};
