<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class TestFormSubmission extends Command
{
    protected $signature = 'test:form-submission';
    protected $description = 'Test the form submission from the schedule-event form';

    public function handle()
    {
        $this->info('=== Testing Form Submission ===\n');

        try {
            // Simulate form data as it would come from the form
            $formData = [
                'event_title' => 'Full Day Workshop',
                'start_date' => '2026-03-25',
                'end_date' => '2026-03-25',
                'start_time' => '08:30',
                'end_time' => '16:30',
                'location' => 'Science Building - Room 101',
                'students' => [
                    [
                        'name' => 'Alice Anderson',
                        'snumber' => 'SA2024001',
                        'section' => 'BSCS-A1',
                        'rfid' => 'AA001'
                    ],
                    [
                        'name' => 'Bob Bennett',
                        'snumber' => 'SB2024002',
                        'section' => 'BSCS-B1',
                        'rfid' => 'AB002'
                    ],
                    [
                        'name' => 'Carol Chen',
                        'snumber' => 'SC2024003',
                        'section' => 'BSIT-A1',
                        'rfid' => null
                    ]
                ]
            ];

            // Validate the data (same validation as in EventController)
            $validator = Validator::make($formData, [
                'event_title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'location' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $this->error('Validation failed:');
                foreach ($validator->errors()->all() as $error) {
                    $this->line("  ✗ {$error}");
                }
                return;
            }

            $this->line('✓ Validation passed\n');

            // Create the event (simulating EventController::store())
            $event = Event::create([
                'e_name' => $formData['event_title'],
                'start_date' => $formData['start_date'],
                'end_date' => $formData['end_date'],
                'start_time' => $formData['start_time'],
                'end_time' => $formData['end_time'],
                'e_location' => $formData['location'],
                'e_status' => 'active',
            ]);

            $this->line("✓ Event created (ID: {$event->e_id})");
            $this->line("  Name: {$event->e_name}");
            $this->line("  Date: {$event->start_date}");
            $this->line("  Location: {$event->e_location}");

            // Register students
            foreach ($formData['students'] as $studentData) {
                $student = \App\Models\Student::updateOrCreate(
                    ['snumber' => $studentData['snumber']],
                    [
                        'name' => $studentData['name'],
                        'section' => $studentData['section'],
                        'rfid' => $studentData['rfid'] ?? null,
                    ]
                );

                Attendance::create([
                    'event_id' => $event->e_id,
                    'snumber' => $student->snumber,
                ]);

                $this->line("✓ Added student: {$student->name} ({$student->snumber})");
            }

            // Verify the data was saved
            $this->line("\n=== Verification ===");
            $savedEvent = Event::with('attendances.student')->find($event->e_id);

            $attendanceRecords = Attendance::where('event_id', $event->e_id)->with('student')->get();
            $this->line("Total students registered: " . count($attendanceRecords));

            foreach ($attendanceRecords as $attendance) {
                $this->line("  - {$attendance->student->name} ({$attendance->student->snumber}) - Section: {$attendance->student->section}");
                if ($attendance->student->rfid) {
                    $this->line("    RFID: {$attendance->student->rfid}");
                }
            }

            $this->info("\n✓ Form submission test PASSED!\n");
            $this->line("The database now contains:");
            $this->line("  • 1 Event: '{$event->e_name}' on {$event->start_date}");
            $this->line("  • " . count($attendanceRecords) . " Attendance records");

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
