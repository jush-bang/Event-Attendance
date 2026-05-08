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
            // Add archive-related columns if they don't exist
            if (!Schema::hasColumn('tbl_event', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('tbl_event', 'archived_delete_at')) {
                $table->timestamp('archived_delete_at')->nullable()->after('archived_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_event', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'archived_delete_at']);
        });
    }
};
