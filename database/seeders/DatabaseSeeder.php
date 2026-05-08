<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed users table with proper role column (lowercase)
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
    }
}
