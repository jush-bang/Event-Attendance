<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Attempting to fix database constraints...\n";
    
    // Drop the problematic unique constraints
    DB::statement('ALTER TABLE tbl_attendance DROP INDEX IF EXISTS unique_event_student');
    echo "✓ Dropped unique_event_student index\n";
    
    DB::statement('ALTER TABLE tbl_attendance DROP INDEX IF EXISTS unique_event_session_student');
    echo "✓ Dropped unique_event_session_student index\n";
    
    // Add the correct constraint for per-session tracking
    DB::statement('ALTER TABLE tbl_attendance ADD UNIQUE KEY unique_event_session_student (event_id, session_id, snumber)');
    echo "✓ Added new unique_event_session_student constraint\n";
    
    echo "\n✅ Database constraints fixed successfully!\n";
    echo "You can now mark attendance per session.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
