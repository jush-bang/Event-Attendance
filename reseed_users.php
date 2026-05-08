<?php
// Clear users table and reseed
require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

try {
    echo "Clearing users table...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('users')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "Users table cleared.\n\n";

    echo "Reseeding users...\n";
    DB::table('users')->insert([
        [
            'name' => 'Admin',
            'email' => 'admin@balagtas.sti.edu.ph',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'scanner_001',
            'email' => 'scanner.001@balagtas.sti.edu.ph',
            'password' => bcrypt('scanner123'),
            'role' => 'scanner',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'scanner_002',
            'email' => 'scanner.002@balagtas.sti.edu.ph',
            'password' => bcrypt('scanner123'),
            'role' => 'scanner',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    
    echo "Users reseeded successfully!\n\n";
    
    echo "=== Current users in database ===\n";
    $users = DB::table('users')->get();
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
