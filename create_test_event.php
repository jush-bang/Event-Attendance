<?php
// Create test event
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$db = $app['db'];

try {
    echo "Creating test event...\n";
    $db->table('tbl_event')->insert([
        'e_name' => 'Test Event for Scanning',
        'start_date' => '2026-05-05',
        'end_date' => '2026-05-05',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'e_location' => 'Test Location',
        'e_status' => 'active',
        'require_action_prompts' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Event created successfully!\n\n";
    
    echo "=== Current events in database ===\n";
    $events = $db->table('tbl_event')->get();
    foreach ($events as $event) {
        echo "ID: {$event->e_id}, Name: {$event->e_name}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}