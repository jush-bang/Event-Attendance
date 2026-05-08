<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    const SCAN_COOLDOWN_SECONDS = 30;

    /**
     * Check if a student can be scanned (30-second cooldown)
     */
    public static function canScan(?Attendance $attendanceRecord): bool
    {
        if (!$attendanceRecord || !$attendanceRecord->last_scan_time) {
            return true;
        }

        try {
            // Get timestamps safely
            $lastScan = $attendanceRecord->last_scan_time instanceof \Carbon\Carbon 
                ? $attendanceRecord->last_scan_time 
                : \Carbon\Carbon::parse($attendanceRecord->last_scan_time);
            
            $now = \Carbon\Carbon::now();
            
            // Use getTimestamp() method instead of property
            $lastTimestamp = $lastScan->getTimestamp();
            $nowTimestamp = $now->getTimestamp();
            $secondsElapsed = $nowTimestamp - $lastTimestamp;
            
            return $secondsElapsed >= self::SCAN_COOLDOWN_SECONDS;
        } catch (\Exception $e) {
            Log::error('canScan Exception: ' . $e->getMessage(), [
                'last_scan_time' => $attendanceRecord->last_scan_time ?? 'null'
            ]);
            // On error, allow the scan (conservative approach)
            return true;
        }
    }

    /**
     * Get seconds remaining for cooldown
     */
    public static function getCooldownRemaining(?Attendance $attendanceRecord): int
    {
        if (!$attendanceRecord || !$attendanceRecord->last_scan_time) {
            return 0;
        }

        try {
            // Get timestamps safely
            $lastScan = $attendanceRecord->last_scan_time instanceof \Carbon\Carbon 
                ? $attendanceRecord->last_scan_time 
                : \Carbon\Carbon::parse($attendanceRecord->last_scan_time);
            
            $now = \Carbon\Carbon::now();
            
            // Use getTimestamp() method instead of property
            $lastTimestamp = $lastScan->getTimestamp();
            $nowTimestamp = $now->getTimestamp();
            $secondsElapsed = $nowTimestamp - $lastTimestamp;
            
            $remaining = self::SCAN_COOLDOWN_SECONDS - $secondsElapsed;
            return max(0, $remaining);
        } catch (\Exception $e) {
            Log::error('getCooldownRemaining Exception: ' . $e->getMessage(), [
                'last_scan_time' => $attendanceRecord->last_scan_time ?? 'null'
            ]);
            // On error, return full cooldown
            return self::SCAN_COOLDOWN_SECONDS;
        }
    }

    /**
     * Record a scan event - creates new cycle or updates existing
     * Action: 'time_in', 'break', or 'time_out'
     */
    public static function recordScan(Attendance $attendance, string $action): Attendance
    {
        $now = \Carbon\Carbon::now();
        $cycles = self::getCycles($attendance);

        Log::info('recordScan started', [
            'attendance_id' => $attendance->id,
            'action' => $action,
            'current_time' => $now->toDateTimeString(),
            'current_cycles' => count($cycles)
        ]);

        if ($action === 'time_in') {
            // Start new cycle
            $cycles[] = [
                'time_in' => $now->toIso8601String(),
                'break_time' => null,
                'time_out' => null,
                'duration_minutes' => 0,
            ];
            $attendance->status = 'present';
        } elseif ($action === 'break') {
            // Mark break time on last cycle
            if (!empty($cycles)) {
                $cycles[count($cycles) - 1]['break_time'] = $now->toIso8601String();
                $cycles[count($cycles) - 1]['duration_minutes'] = self::calculateDuration(
                    $cycles[count($cycles) - 1]['time_in'],
                    $cycles[count($cycles) - 1]['break_time']
                );
            }
            $attendance->status = 'left_session';
        } elseif ($action === 'time_out') {
            // Mark time out on last cycle
            if (!empty($cycles)) {
                $cycles[count($cycles) - 1]['time_out'] = $now->toIso8601String();
                $cycles[count($cycles) - 1]['duration_minutes'] = self::calculateDuration(
                    $cycles[count($cycles) - 1]['time_in'],
                    $cycles[count($cycles) - 1]['time_out']
                );
            }
            $attendance->status = 'present';
        } elseif ($action === 'return_from_break') {
            // Start new cycle after break
            $cycles[] = [
                'time_in' => $now->toIso8601String(),
                'break_time' => null,
                'time_out' => null,
                'duration_minutes' => 0,
            ];
            $attendance->status = 'present';
        }

        // Update attendance record
        $attendance->cycles_data = json_encode($cycles);
        $attendance->total_duration_minutes = self::calculateTotalDuration($cycles);
        $attendance->last_scan_time = $now;
        
        // Set time_in on first scan
        if ($action === 'time_in' && is_null($attendance->time_in)) {
            $attendance->time_in = $now;
        }
        
        // CRITICAL: Save to database
        $attendance->save();

        Log::info('recordScan completed', [
            'attendance_id' => $attendance->id,
            'action' => $action,
            'last_scan_time_set_to' => $attendance->last_scan_time,
            'status' => $attendance->status,
            'cycles_count' => count($cycles),
            'total_duration_minutes' => $attendance->total_duration_minutes
        ]);

        return $attendance;
    }

    /**
     * Reset cooldown for next action
     * Call this after an action is processed (break/time_out/return_from_break)
     */
    public static function resetCooldown(Attendance $attendance): Attendance
    {
        Log::info('resetCooldown called', [
            'attendance_id' => $attendance->id,
            'previous_last_scan_time' => $attendance->last_scan_time,
            'action' => 'Setting last_scan_time to null'
        ]);
        
        $attendance->last_scan_time = null;
        $attendance->save();
        
        Log::info('resetCooldown completed', [
            'attendance_id' => $attendance->id,
            'new_last_scan_time' => $attendance->last_scan_time,
            'saved' => true
        ]);
        
        return $attendance;
    }

    /**
     * Get cycles array from JSON
     */
    public static function getCycles(Attendance $attendance): array
    {
        if (is_array($attendance->cycles_data)) {
            return $attendance->cycles_data;
        }

        if (!$attendance->cycles_data) {
            return [];
        }

        return json_decode($attendance->cycles_data, true) ?? [];
    }

    /**
     * Calculate duration between two timestamps in minutes
     */
    private static function calculateDuration(?string $startTime, ?string $endTime): int
    {
        if (!$startTime || !$endTime) {
            return 0;
        }

        try {
            $start = \Carbon\Carbon::parse($startTime);
            $end = \Carbon\Carbon::parse($endTime);

            // Use Unix timestamps for timezone-independent calculation
            $startUnix = $start->getTimestamp();
            $endUnix = $end->getTimestamp();
            $secondsDiff = $endUnix - $startUnix;
            
            // Convert to minutes
            $minutes = (int)($secondsDiff / 60);
            
            // Ensure it's non-negative (data integrity check)
            return max(0, $minutes);
        } catch (\Exception $e) {
            Log::error('calculateDuration Exception: ' . $e->getMessage(), [
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);
            return 0;
        }
    }

    /**
     * Calculate total duration from all cycles
     */
    private static function calculateTotalDuration(array $cycles): int
    {
        $total = 0;

        foreach ($cycles as $cycle) {
            $total += $cycle['duration_minutes'] ?? 0;
        }

        return $total;
    }

    /**
     * Format minutes to "2hrs 30m" format
     */
    public static function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return "{$mins}m";
        }

        if ($mins === 0) {
            return $hours === 1 ? "1hr" : "{$hours}hrs";
        }

        return $hours === 1 ? "1hr {$mins}m" : "{$hours}hrs {$mins}m";
    }

    /**
     * Get last cycle for a student
     */
    public static function getLastCycle(Attendance $attendance): ?array
    {
        $cycles = self::getCycles($attendance);

        return !empty($cycles) ? end($cycles) : null;
    }

    /**
     * Get current duration display (for live updates while student is still in)
     */
    public static function getCurrentCycleDuration(Attendance $attendance): string
    {
        $lastCycle = self::getLastCycle($attendance);

        if (!$lastCycle || !$lastCycle['time_in']) {
            return '0m';
        }

        $startTime = \Carbon\Carbon::parse($lastCycle['time_in']);
        $now = \Carbon\Carbon::now();
        $minutes = $now->diffInMinutes($startTime);

        return self::formatDuration($minutes);
    }
}
