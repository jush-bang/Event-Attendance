<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAttendanceConstraints extends Command
{
    protected $signature = 'check:constraints';

    protected $description = 'Check all constraints on tbl_attendance';

    public function handle()
    {
        $this->info('=== Checking Constraints on tbl_attendance ===');
        
        try {
            $constraints = DB::select("SHOW KEYS FROM tbl_attendance");
            
            $this->table(
                ['Key Name', 'Column Name', 'Seq', 'Unique', 'Index Type'],
                array_map(fn($k) => [
                    $k->Key_name,
                    $k->Column_name,
                    $k->Seq_in_index,
                    $k->Non_unique ? 'No' : 'Yes',
                    $k->Index_type ?? 'BTREE'
                ], $constraints)
            );
            
            $this->info('Total keys: ' . count($constraints));
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
