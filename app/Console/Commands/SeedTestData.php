<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Student;
use App\Models\Session;
use Carbon\Carbon;

class SeedTestData extends Command
{
    protected $signature = 'seed:test-data';

    protected $description = 'Seed test data for attendance testing';

    public function handle()
    {
        $this->info('Seeding test data...');

        // Create test event
        $event = Event::create([
            'e_name' => 'Intrams',
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'e_location' => 'Sports Complex',
            'e_status' => 'active',
            'sessions' => 2,
        ]);

        $this->info("✅ Created event: {$event->e_name} (ID: {$event->e_id})");

        // Create test students
        $studentData = [
            ['snumber' => '02200031231', 'name' => 'John Doe', 'rfid' => 'RFID001', 'section' => 'Section A'],
            ['snumber' => '02200031232', 'name' => 'Jane Smith', 'rfid' => 'RFID002', 'section' => 'Section A'],
            ['snumber' => '02200031233', 'name' => 'Bob Johnson', 'rfid' => 'RFID003', 'section' => 'Section B'],
            ['snumber' => '02200031234', 'name' => 'Alice Brown', 'rfid' => 'RFID004', 'section' => 'Section B'],
            ['snumber' => '02200031235', 'name' => 'Charlie Wilson', 'rfid' => 'RFID005', 'section' => 'Section C'],
        ];

        foreach ($studentData as $data) {
            Student::firstOrCreate(
                ['snumber' => $data['snumber']],
                $data
            );
        }

        $this->info('✅ Created ' . count($studentData) . ' test students');

        // Create test sessions
        $now = Carbon::now();
        
        $session1 = Session::create([
            'event_id' => $event->e_id,
            'session_number' => 1,
            'day_number' => 1,
            'session_date' => $now->toDateString(),
            'start_time' => $now->copy()->setTime(9, 0, 0),
            'status' => 'active',
        ]);

        $session2 = Session::create([
            'event_id' => $event->e_id,
            'session_number' => 2,
            'day_number' => 1,
            'session_date' => $now->toDateString(),
            'start_time' => $now->copy()->setTime(13, 0, 0),
            'status' => 'upcoming',
        ]);

        $this->info('✅ Created 2 test sessions');
        $this->info("\n📋 Test Data Summary:");
        $this->info("  Event: {$event->e_name} (ID: {$event->e_id})");
        $this->info("  Students: " . count($studentData));
        $this->info("  Sessions: 2 (Active: Session {$session1->id})");
        $this->info("\n✨ Test data seeded successfully!");
    }
}
