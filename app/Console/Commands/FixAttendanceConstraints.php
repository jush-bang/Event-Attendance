<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAttendanceConstraints extends Command
{
    protected $signature = 'fix:constraints';
    protected $description = 'Fix attendance table unique constraints for per-session tracking';

    public function handle()
    {
        try {
            $this->info('Fixing database constraints...');
            
            // Drop the problematic unique constraints (without IF EXISTS for MySQL 5.7 compatibility)
            try {
                DB::statement('ALTER TABLE tbl_attendance DROP INDEX unique_event_student');
                $this->info('✓ Dropped unique_event_student index');
            } catch (\Exception $e) {
                $this->info('• unique_event_student index not found (OK)');
            }
            
            try {
                DB::statement('ALTER TABLE tbl_attendance DROP INDEX unique_event_session_student');
                $this->info('✓ Dropped unique_event_session_student index');
            } catch (\Exception $e) {
                $this->info('• unique_event_session_student index not found (OK)');
            }
            
            // Add the correct constraint for per-session tracking
            DB::statement('ALTER TABLE tbl_attendance ADD UNIQUE KEY unique_event_session_student (event_id, session_id, snumber)');
            $this->info('✓ Added new unique_event_session_student constraint');
            
            $this->info("\n✅ Database constraints fixed successfully!");
            $this->info("You can now mark attendance per session.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
