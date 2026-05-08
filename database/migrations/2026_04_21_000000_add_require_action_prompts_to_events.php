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
            $table->boolean('require_action_prompts')->default(true)->after('sessions')->comment('If true, show action modal on second scan. If false, auto timeout on second scan.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_event', function (Blueprint $table) {
            $table->dropColumn('require_action_prompts');
        });
    }
};
