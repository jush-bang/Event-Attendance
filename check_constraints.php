<?php
require 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$constraints = DB::select("SHOW KEYS FROM tbl_attendance WHERE Key_name LIKE '%unique%'");
echo "=== Unique Constraints on tbl_attendance ===\n";
foreach ($constraints as $key) {
    echo "Key Name: " . $key->Key_name . "\n";
    echo "Column Name: " . $key->Column_name . "\n";
    echo "Seq in Index: " . $key->Seq_in_index . "\n";
    echo "---\n";
}

// Also check all keys
echo "\n=== All Keys on tbl_attendance ===\n";
$allKeys = DB::select("SHOW KEYS FROM tbl_attendance");
foreach ($allKeys as $key) {
    echo "Key: " . $key->Key_name . " | Column: " . $key->Column_name . " | Unique: " . $key->Non_unique . "\n";
}
