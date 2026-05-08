<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with all events
     */
    public function index()
    {
        // Fetch events with pagination (10 per page) and attendance details
        // Exclude archived events from the main view
        $events = Event::with(['attendances.student', 'user'])
            ->whereNull('archived_at')
            ->orderByDesc('created_at')
            ->paginate(8);

        // Calculate attendance stats for each event
        $events->getCollection()->transform(function ($event) {
            // Count unique students (not attendance records)
            $totalStudents = $event->attendances->pluck('snumber')->unique()->count();
            
            // Calculate percentage of students with time_in recorded
            $checkedIn = $event->attendances->filter(fn($a) => $a->time_in !== null)->pluck('snumber')->unique()->count();
            $attendancePercentage = $totalStudents > 0 ? round(($checkedIn / $totalStudents) * 100) : 0;
            
            // Determine event status
            $now = Carbon::now();
            $eventStart = Carbon::createFromFormat('Y-m-d H:i:s', $event->start_date->format('Y-m-d') . ' ' . $event->start_time);
            $eventEnd = Carbon::createFromFormat('Y-m-d H:i:s', $event->end_date->format('Y-m-d') . ' ' . $event->end_time);
            
            if ($eventStart > $now) {
                $status = 'upcoming';
            } elseif ($eventEnd < $now) {
                $status = 'completed';
            } else {
                $status = 'live';
            }
            
            $event->status = $status;
            $event->attendancePercentage = $attendancePercentage;
            $event->totalStudents = $totalStudents;
            $event->checkedIn = $checkedIn;
            
            return $event;
        });

        return view('dashboard', [
            'events' => $events,
        ]);
    }
}
