<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateSampleEvents extends Command
{
    protected $signature = 'create:sample-events';
    protected $description = 'Create sample events for dashboard testing';

    public function handle()
    {
        $this->info('Creating sample events for testing...\n');

        $events = [
            [
                'e_name' => 'Mobile Development Workshop',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'start_time' => Carbon::now()->subHours(2)->format('H:i'),
                'end_time' => Carbon::now()->addHours(2)->format('H:i'),
                'e_location' => 'Tech Lab - Building A',
                'status' => 'live',
                'students' => [
                    ['name' => 'Emma Wilson', 'snumber' => 'EW2024001', 'section' => 'BSCS-C1', 'rfid' => 'EW001'],
                    ['name' => 'Frank Davis', 'snumber' => 'FD2024002', 'section' => 'BSCS-C1', 'rfid' => 'FD002'],
                ]
            ],
            [
                'e_name' => 'Python Basics Seminar',
                'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '12:00',
                'e_location' => 'Auditorium B',
                'status' => 'upcoming',
                'students' => [
                    ['name' => 'Grace Lee', 'snumber' => 'GL2024003', 'section' => 'BSIT-A1', 'rfid' => 'GL003'],
                    ['name' => 'Henry Brown', 'snumber' => 'HB2024004', 'section' => 'BSIT-B1', 'rfid' => 'HB004'],
                ]
            ],
            [
                'e_name' => 'Web Design Conference',
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '15:00',
                'e_location' => 'Convention Center',
                'status' => 'completed',
                'students' => [
                    ['name' => 'Iris Martinez', 'snumber' => 'IM2024005', 'section' => 'BSCS-A1', 'rfid' => 'IM005'],
                    ['name' => 'Jack Smith', 'snumber' => 'JS2024006', 'section' => 'BSCS-B1', 'rfid' => 'JS006'],
                    ['name' => 'Karen Jones', 'snumber' => 'KJ2024007', 'section' => 'BSIT-A1', 'rfid' => 'KJ007'],
                ]
            ],
        ];

        foreach ($events as $eventData) {
            $students = $eventData['students'];
            unset($eventData['students']);
            unset($eventData['status']);

            $event = Event::create($eventData);
            $this->line("✓ Created event: {$event->e_name} (ID: {$event->e_id})");

            foreach ($students as $studentData) {
                $student = Student::updateOrCreate(
                    ['snumber' => $studentData['snumber']],
                    $studentData
                );

                Attendance::create([
                    'event_id' => $event->e_id,
                    'snumber' => $student->snumber,
                    'time_in' => rand(0, 1) ? Carbon::now()->subHours(rand(1, 5)) : null,
                ]);

                $this->line("  • Added student: {$student->name}");
            }
        }

        $this->info("\n✓ Sample events created successfully!");
    }
}
