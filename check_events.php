<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$db = $app['db'];

// Check if event 10 exists
$event = $db->table('tbl_event')->where('e_id', 10)->first();

if ($event) {
    echo "✓ Event Found:\n";
    echo "ID: " . $event->e_id . "\n";
    echo "Name: " . $event->e_name . "\n";
    echo "Created At: " . $event->created_at . "\n";
    echo "Updated At: " . $event->updated_at . "\n";
} else {
    echo "✗ Event #10 not found\n";
}

// Show all events
echo "\n\nAll Events:\n";
$allEvents = $db->table('tbl_event')->get();
echo count($allEvents) . " events found\n";
foreach ($allEvents as $e) {
    echo "- ID: {$e->e_id}, Name: {$e->e_name}, Created: {$e->created_at}\n";
}
