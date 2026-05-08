<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicates extends Command
{
    protected $signature = 'check:duplicates {event_id?}';

    protected $description = 'Check for duplicate attendance records';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        
        $query = DB::table('tbl_attendance')
            ->selectRaw('event_id, session_id, snumber, COUNT(*) as cnt')
            ->groupBy('event_id', 'session_id', 'snumber')
            ->having('cnt', '>', 1);
        
        if ($eventId) {
            $query->where('event_id', $eventId);
        }
        
        $duplicates = $query->get();
        
        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate records found!');
        } else {
            $this->error('❌ Found duplicate records:');
            $this->table(
                ['Event ID', 'Session ID', 'Student Number', 'Count'],
                $duplicates->map(fn($d) => [$d->event_id, $d->session_id, $d->snumber, $d->cnt])
            );
        }
    }
}
