<?php
// Quick verification script
$path = __DIR__;
require_once $path . '/vendor/autoload.php';

$app = require_once $path . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Attendance;

$duplicates = Attendance::select('event_id', 'snumber')
    ->groupBy('event_id', 'snumber')
    ->havingRaw('COUNT(*) > 1')
    ->count();

$total = Attendance::count();

echo "=== Database Cleanup Verification ===\n";
echo "Remaining duplicate records: $duplicates\n";
echo "Total attendance records: $total\n";
echo "\nDuplicate cleanup status: " . ($duplicates === 0 ? "✓ SUCCESS - All duplicates removed!" : "✗ FAILED - Duplicates still exist") . "\n";
