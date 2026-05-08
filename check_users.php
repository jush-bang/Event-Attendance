<?php
require 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

echo "=== Users Table Schema ===\n";
$columns = DB::select('DESCRIBE users');
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type}\n";
}

echo "\n=== Users Data ===\n";
$users = DB::select('SELECT * FROM users');
foreach ($users as $user) {
    $role = isset($user->role) ? $user->role : 'NULL';
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$role}\n";
}

echo "\nTotal users: " . count($users) . "\n";
?>
