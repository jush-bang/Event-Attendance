<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;

class CheckEventStatus extends Command
{
    protected $signature = 'check:event {event_id}';

    protected $description = 'Check event and session status';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        $event = Event::find($eventId);
        
        if (!$event) {
            $this->error("Event {$eventId} not found");
            return;
        }
        
        $this->info("Event: {$event->e_name} (ID: {$event->e_id})");
        $this->info("Status: {$event->e_status}");
        
        $sessions = $event->sessions()->get();
        
        if ($sessions->isEmpty()) {
            $this->warn("No sessions for this event");
            return;
        }
        
        $this->table(
            ['Session ID', 'Session Type', 'Status', 'Start Time', 'End Time'],
            $sessions->map(fn($s) => [
                $s->id,
                $s->session_type,
                $s->status,
                $s->start_time,
                $s->end_time
            ])
        );
    }
}
