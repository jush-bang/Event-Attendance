<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class TestMarkAttendance extends Command
{
    protected $signature = 'test:mark-attendance {event_id?} {student_number?} {session_id?}';

    protected $description = 'Test marking a student as present';

    public function handle()
    {
        $eventId = $this->argument('event_id') ?? Event::first()->e_id;
        $studentNumber = $this->argument('student_number') ?? Student::first()->snumber;
        $sessionId = $this->argument('session_id') ?? 1;

        $event = Event::find($eventId);
        if (!$event) {
            $this->error("❌ Event {$eventId} not found");
            return;
        }

        $student = Student::where('snumber', $studentNumber)->first();
        if (!$student) {
            $this->error("❌ Student {$studentNumber} not found");
            return;
        }

        $this->info("Testing attendance mark for:");
        $this->info("  Event: {$event->e_name} (ID: {$event->e_id})");
        $this->info("  Student: {$student->name} ({$student->snumber})");
        $this->info("  Session: {$sessionId}");
        $this->newLine();

        try {
            // Attempt to mark student present (same as the controller does)
            $attendance = Attendance::firstOrCreate(
                [
                    'event_id' => $event->e_id,
                    'session_id' => $sessionId,
                    'snumber' => $student->snumber,
                ],
                ['time_in' => now()]
            );

            $this->info("✅ Successfully marked attendance!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $attendance->id],
                    ['Event', $event->e_name],
                    ['Student', $student->name],
                    ['Session', $attendance->session_id],
                    ['Time In', $attendance->time_in],
                    ['Status', 'Present'],
                ]
            );

        } catch (\Exception $e) {
            $this->error("❌ Error marking attendance: " . $e->getMessage());
        }
    }
}
