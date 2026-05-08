<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;

class CleanupAttendanceRecords extends Command
{
    protected $signature = 'cleanup:attendance {eventId?}';
    protected $description = 'Clean up duplicate/orphaned attendance records';

    public function handle()
    {
        try {
            $eventId = $this->argument('eventId');
            
            if ($eventId) {
                $this->info("Cleaning up attendance records for event {$eventId}...");
                $deleted = Attendance::where('event_id', $eventId)->delete();
            } else {
                $this->info("Cleaning up all orphaned attendance records...");
                // Keep only the first record for each event-session-student combination
                $duplicates = Attendance::selectRaw('event_id, session_id, snumber')
                    ->groupBy('event_id', 'session_id', 'snumber')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
                
                $deleted = 0;
                foreach ($duplicates as $dup) {
                    $toDelete = Attendance::where('event_id', $dup->event_id)
                        ->where('session_id', $dup->session_id)
                        ->where('snumber', $dup->snumber)
                        ->orderBy('id')
                        ->skip(1) // Keep first one
                        ->delete();
                    
                    $deleted += $toDelete;
                }
            }
            
            $this->info("✅ Deleted {$deleted} duplicate/orphaned records");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
