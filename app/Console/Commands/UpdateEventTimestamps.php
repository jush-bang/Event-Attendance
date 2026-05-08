<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateEventTimestamps extends Command
{
    protected $signature = 'update:event-timestamps';
    protected $description = 'Update existing events with created_at timestamps';

    public function handle()
    {
        $this->info('Updating event timestamps...\n');

        $timestamps = [
            1 => Carbon::now()->subDays(10),
            2 => Carbon::now()->subDays(8),
            3 => Carbon::now()->subDays(5),
            4 => Carbon::now()->subDays(2),
            5 => Carbon::now(),
            6 => Carbon::now()->subHours(2),
            7 => Carbon::now()->subDays(6),
        ];

        foreach ($timestamps as $eventId => $createdAt) {
            $event = Event::find($eventId);
            if ($event) {
                $event->update([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $this->line("✓ Event {$eventId} ({$event->e_name}) updated");
            }
        }

        $this->info("\n✓ All events updated with timestamps!");
    }
}
