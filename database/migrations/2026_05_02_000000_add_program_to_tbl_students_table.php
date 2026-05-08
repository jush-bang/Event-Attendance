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
        if (Schema::hasTable('tbl_students') && !Schema::hasColumn('tbl_students', 'program')) {
            Schema::table('tbl_students', function (Blueprint $table) {
                $table->string('program')->nullable()->after('section');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tbl_students') && Schema::hasColumn('tbl_students', 'program')) {
            Schema::table('tbl_students', function (Blueprint $table) {
                $table->dropColumn('program');
            });
        }
    }
};
