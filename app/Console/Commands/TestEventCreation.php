<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Console\Command;

class TestEventCreation extends Command
{
    protected $signature = 'test:event-creation';
    protected $description = 'Test creating an event with students';

    public function handle()
    {
        $this->info('Testing event creation...');

        try {
            // Create an event
            $event = Event::create([
                'e_name' => 'Test Event',
                'start_date' => '2026-03-20',
                'end_date' => '2026-03-20',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'e_location' => 'Main Hall',
                'e_status' => 'active',
            ]);

            $this->line("✓ Event created with ID: {$event->e_id}");

            // Create some test students
            $studentsData = [
                ['snumber' => '2024001', 'name' => 'John Doe', 'section' => 'A1', 'rfid' => 'RF001'],
                ['snumber' => '2024002', 'name' => 'Jane Smith', 'section' => 'A2', 'rfid' => 'RF002'],
                ['snumber' => '2024003', 'name' => 'Bob Johnson', 'section' => 'B1', 'rfid' => 'RF003'],
            ];

            foreach ($studentsData as $studentData) {
                $student = Student::updateOrCreate(
                    ['snumber' => $studentData['snumber']],
                    $studentData
                );
                $this->line("✓ Student created: {$student->name} ({$student->snumber})");

                // Create attendance record
                Attendance::create([
                    'event_id' => $event->e_id,
                    'snumber' => $student->snumber,
                ]);
                $this->line("  ✓ Attendance record created");
            }

            // Verify the data
            $this->line("\n=== Verification ===");
            $createdEvent = Event::find($event->e_id);
            $this->line("Event: {$createdEvent->e_name} on {$createdEvent->start_date}");
            $this->line("Location: {$createdEvent->e_location}");

            $attendanceCount = Attendance::where('event_id', $event->e_id)->count();
            $this->line("Total attendance records: {$attendanceCount}");

            $attendances = Attendance::where('event_id', $event->e_id)->get();
            foreach ($attendances as $att) {
                $this->line("  - {$att->student->name} ({$att->snumber})");
            }

            $this->info("\n✓ All tests passed!");

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
