<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestEventSave extends Command
{
    protected $signature = 'test:event-save';
    protected $description = 'Test creating an event with timestamps';

    public function handle()
    {
        try {
            $this->info('Testing Event Creation with Timestamps...');

            // Create an event
            $event = Event::create([
                'e_name' => 'Test Event with Timestamps - ' . now()->format('Y-m-d H:i:s'),
                'start_date' => '2026-03-20',
                'end_date' => '2026-03-20',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'e_location' => 'Test Hall',
                'e_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info('✓ Event created successfully!');
            $this->info('Event ID: ' . $event->e_id);
            $this->info('Event Name: ' . $event->e_name);
            $this->info('Created At: ' . $event->created_at);
            $this->info('Updated At: ' . $event->updated_at);

            // Create a student
            $student = Student::updateOrCreate(
                ['snumber' => 'TEST001'],
                [
                    'name' => 'Test Student',
                    'section' => 'A',
                    'rfid' => null,
                ]
            );

            $this->info('✓ Student created/updated successfully!');

            // Create attendance record
            $attendance = Attendance::create([
                'event_id' => $event->e_id,
                'snumber' => $student->snumber,
            ]);

            $this->info('✓ Attendance record created successfully!');
            $this->info('All operations completed successfully!');

        } catch (\Exception $e) {
            $this->error('✗ Error occurred:');
            $this->error($e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            $this->error('\nFull Stack:');
            $this->error($e);
        }
    }
}
