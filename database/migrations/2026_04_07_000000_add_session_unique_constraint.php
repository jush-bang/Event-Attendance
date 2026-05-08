<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if constraint already exists and skip if it does
        $constraints = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME='tbl_attendance' AND CONSTRAINT_NAME='unique_event_session_student' AND TABLE_SCHEMA=DATABASE()");
        
        if (count($constraints) === 0) {
            Schema::table('tbl_attendance', function (Blueprint $table) {
                $table->unique(['event_id', 'session_id', 'snumber'], 'unique_event_session_student');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_attendance', function (Blueprint $table) {
            $table->dropUnique('unique_event_session_student');
        });
    }
};
