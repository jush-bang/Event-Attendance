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
        // Check if the column already exists before adding it
        if (!Schema::hasColumn('tbl_sessions', 'user_id')) {
            Schema::table('tbl_sessions', function (Blueprint $table) {
                // Add user_id column to track who started the session
                // Use foreignId helper which automatically handles the correct type
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tbl_sessions', 'user_id')) {
            Schema::table('tbl_sessions', function (Blueprint $table) {
                $table->dropForeignIdFor('users', 'user_id');
            });
        }
    }
};
