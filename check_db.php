<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

echo "=== Checking tbl_attendance schema ===\n";
$columns = DB::select("DESCRIBE tbl_attendance");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} - {$col->Null}/{$col->Key}\n";
}

echo "\n=== Checking tbl_students schema ===\n";
$columns = DB::select("DESCRIBE tbl_students");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} - {$col->Null}/{$col->Key}\n";
}

echo "\n=== Checking tbl_event schema ===\n";
$columns = DB::select("DESCRIBE tbl_event");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} - {$col->Null}/{$col->Key}\n";
}

echo "\n=== Checking attendance records ===\n";
$records = DB::select("SELECT * FROM tbl_attendance");
echo "Total records: " . count($records) . "\n";
foreach ($records as $record) {
    echo "Event: {$record->event_id}, Student: {$record->snumber}, In: {$record->time_in}, Out: {$record->time_out}\n";
}
?>
