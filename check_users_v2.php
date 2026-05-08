<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

try {
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
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
