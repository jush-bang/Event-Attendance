<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/debug-users', function() {
    $users = User::all();
    echo "<pre>";
    echo "=== Users Table Data ===\n";
    echo json_encode($users, JSON_PRETTY_PRINT);
    echo "</pre>";
    
    echo "<pre>";
    echo "=== Table Columns ===\n";
    $columns = \Illuminate\Support\Facades\DB::select("DESCRIBE users");
    foreach ($columns as $col) {
        echo "{$col->Field}: {$col->Type} (Null: {$col->Null}, Key: {$col->Key})\n";
    }
    echo "</pre>";
});
