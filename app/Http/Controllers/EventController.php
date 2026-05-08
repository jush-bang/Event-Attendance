<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Session;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Show the schedule event page
     */
    public function create()
    {
        $students = Student::all();
        return view('schedule-event', ['students' => $students]);
    }

    /**
     * Store a new event with selected students
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'sessions' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'require_action_prompts' => 'nullable|boolean',
        ]);

        try {
            // Debug logging
            Log::info('[DEBUG] Raw require_action_prompts input:', [
                'input' => $request->input('require_action_prompts'),
                'has' => $request->has('require_action_prompts'),
                'bool' => (bool)$request->input('require_action_prompts'),
                'equals_1' => $request->input('require_action_prompts') === '1',
            ]);
            
            // Create the event with explicit timestamps
            $event = Event::create([
                'e_name' => $validated['event_title'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'sessions' => $validated['sessions'],
                'e_location' => $validated['location'],
                'require_action_prompts' => $request->input('require_action_prompts') === '1',
                'e_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('[DEBUG] Event created with require_action_prompts:', [
                'value' => $event->require_action_prompts
            ]);

            // Auto-generate sessions for the event
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $sessionCount = (int)$validated['sessions'];
            
            // Calculate number of days
            $numDays = $startDate->diffInDays($endDate) + 1;
            
            // Create session records for each day and session
            for ($day = 1; $day <= $numDays; $day++) {
                $sessionDate = $startDate->clone()->addDays($day - 1);
                
                for ($session = 1; $session <= $sessionCount; $session++) {
                    Session::create([
                        'event_id' => $event->getKey(),
                        'session_number' => $session,
                        'day_number' => $day,
                        'session_date' => $sessionDate->format('Y-m-d'),
                        'status' => 'upcoming',
                        'start_time' => null,
                        'end_time' => null,
                    ]);
                }
            }

            return redirect()->route('dashboard')->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            Log::error('Event creation error: ' . $e->getMessage());
            Log::error($e);
            return back()->withErrors(['error' => 'Error creating event: ' . $e->getMessage()]);
        }
    }

    /**
     * Add a student to an event
     */
    public function addStudent(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:tbl_event,e_id',
            'student_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50',
            'section' => 'required|string|max:50',
            'program' => 'nullable|string|max:100',
            'rfid' => 'nullable|string|max:100',
        ]);

        Student::create([
            'snumber' => $validated['student_id'],
            'e_id' => $validated['event_id'],
            'name' => $validated['student_name'],
            'section' => $validated['section'],
            'program' => $validated['program'] ?? null,
            'rfid' => $validated['rfid'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student added successfully!',
        ]);
    }

    /**
     * Remove a student from an event
     */
    public function removeStudent(int $studentId)
    {
        $student = Student::findOrFail($studentId);
        $eventId = $student->e_id;
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student removed successfully!',
        ]);
    }

    /**
     * Get students for an event (for AJAX)
     */
    public function getEventStudents(int $eventId)
    {
        $students = Student::where('e_id', $eventId)->get();
        return response()->json($students);
    }

    /**
     * Show the event detail page with attendance tracking
     */
    public function showDetail(int $eventId)
    {
        $perPage = 30;

        // Fetch event and basic data
        [$event, $activeSession, $allStudentNumbers, $students, $studentsByNumber, $attendancesByStudent] = $this->getEventData($eventId);

        // Build paginated attendee list
        $uniqueStudents = $this->getAttendeeList($event, $allStudentNumbers, $attendancesByStudent, $perPage);

        // Process sessions with all students
        $sessions = $this->getProcessedSessions($event, $students, $studentsByNumber);

        // Get daily attendance data
        $attendances = $this->getDailyAttendances($event, $activeSession, $allStudentNumbers, $studentsByNumber);

        // Calculate attendance statistics
        [$totalStudents, $checkedIn, $absent, $attendancePercentage] = $this->calculateAttendanceStats($attendances, $activeSession, $allStudentNumbers);

        $startDate = \Carbon\Carbon::parse($event->start_date);
        $endDate = \Carbon\Carbon::parse($event->end_date);
        $numDays = $startDate->diffInDays($endDate) + 1;

        return view('event-detail', [
            'event' => $event,
            'attendances' => $attendances,
            'uniqueStudents' => $uniqueStudents,
            'sessions' => $sessions,
            'totalStudents' => $totalStudents,
            'checkedIn' => $checkedIn,
            'absent' => $absent,
            'attendancePercentage' => $attendancePercentage,
            'numDays' => $numDays,
            'activeSession' => $activeSession,
        ]);
    }

    /**
     * Fetch a single attendee list page for AJAX pagination
     */
    public function getAttendeePage(int $eventId)
    {
        $perPage = 30;

        [$event, $activeSession, $allStudentNumbers, $students, $studentsByNumber, $attendancesByStudent] = $this->getEventData($eventId);
        $uniqueStudents = $this->getAttendeeList($event, $allStudentNumbers, $attendancesByStudent, $perPage);

        $rowsHtml = view('event.partials.attendee-list-rows', ['uniqueStudents' => $uniqueStudents])->render();

        return response()->json([
            'success' => true,
            'rowsHtml' => $rowsHtml,
            'from' => $uniqueStudents->count() ? $uniqueStudents->firstItem() : 0,
            'to' => $uniqueStudents->count() ? $uniqueStudents->lastItem() : 0,
            'total' => $uniqueStudents->total(),
            'currentPage' => $uniqueStudents->currentPage(),
            'lastPage' => $uniqueStudents->lastPage(),
            'hasMorePages' => $uniqueStudents->hasMorePages(),
            'onFirstPage' => $uniqueStudents->onFirstPage(),
        ]);
    }

    /**
     * Get basic event data and preload related models
     */
    private function getEventData(int $eventId): array
    {
        $event = Event::with(['attendances.student', 'user'])
            ->findOrFail($eventId);

        $activeSession = Session::where('event_id', $eventId)
            ->where('status', 'active')
            ->first();

        $allStudentNumbers = $event->attendances->pluck('snumber')->unique();
        $students = Student::whereIn('snumber', $allStudentNumbers)->get();
        $studentsByNumber = $students->keyBy('snumber');
        $attendancesByStudent = $event->attendances->groupBy('snumber');

        return [$event, $activeSession, $allStudentNumbers, $students, $studentsByNumber, $attendancesByStudent];
    }

    /**
     * Build paginated attendee list with optional search
     */
    private function getAttendeeList(Event $event, Collection $allStudentNumbers, Collection $attendancesByStudent, int $perPage): LengthAwarePaginator
    {
        $searchTerm = trim(request('attendee_search', ''));

        $studentQuery = Student::whereIn('snumber', $allStudentNumbers);

        if ($searchTerm !== '') {
            $studentQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('snumber', 'like', "%{$searchTerm}%");
            });
        }

        $studentPage = $studentQuery
            ->orderBy('name')
            ->paginate($perPage)
            ->appends(['attendee_search' => $searchTerm]);

        $attendancePageItems = collect($studentPage->items())->map(function ($student) use ($attendancesByStudent) {
            $firstAttendance = $attendancesByStudent->get($student->snumber)?->first();

            if ($firstAttendance) {
                $firstAttendance->setRelation('student', $student);
                return $firstAttendance;
            }

            $tempAttendance = new Attendance();
            $tempAttendance->fill([
                'event_id' => null,
                'snumber' => $student->snumber,
                'time_in' => null,
                'time_out' => null,
            ]);
            $tempAttendance->setRelation('student', $student);
            return $tempAttendance;
        });

        return new LengthAwarePaginator(
            $attendancePageItems,
            $studentPage->total(),
            $studentPage->perPage(),
            $studentPage->currentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Process sessions to include all students (including absent ones)
     */
    private function getProcessedSessions(Event $event, Collection $students, Collection $studentsByNumber): Collection
    {
        $sessions = $event->sessions()
            ->with('attendances.student', 'user')
            ->orderBy('day_number')
            ->orderBy('session_number')
            ->get();

        return $sessions->map(function ($session) use ($students) {
            $sessionAttendances = $session->attendances;
            $allSessionAttendances = collect();

            foreach ($students as $student) {
                $attendance = $sessionAttendances->firstWhere('snumber', $student->snumber);

                if ($attendance) {
                    $allSessionAttendances->push($attendance);
                } else {
                    $tempAttendance = new Attendance();
                    $tempAttendance->event_id = $session->event_id;
                    $tempAttendance->snumber = $student->snumber;
                    $tempAttendance->session_id = $session->getKey();
                    $tempAttendance->time_in = null;
                    $tempAttendance->time_out = null;
                    $tempAttendance->setRelation('student', $student);
                    $allSessionAttendances->push($tempAttendance);
                }
            }

            $session->setRelation('attendances', $allSessionAttendances);
            return $session;
        });
    }

    /**
     * Get daily attendance data for the active session or all attendances
     */
    private function getDailyAttendances(Event $event, ?Session $activeSession, Collection $allStudentNumbers, Collection $studentsByNumber): Collection
    {
        if ($activeSession) {
            $attendances = $event->attendances
                ->where('session_id', $activeSession->getKey())
                ->values();

            foreach ($allStudentNumbers as $snumber) {
                if (!$attendances->firstWhere('snumber', $snumber)) {
                    $student = $studentsByNumber->get($snumber);
                    if ($student) {
                        $tempAttendance = new Attendance();
                        $tempAttendance->fill([
                            'event_id' => $event->getKey(),
                            'snumber' => $snumber,
                            'session_id' => $activeSession->getKey(),
                            'time_in' => null,
                            'time_out' => null,
                        ]);
                        $tempAttendance->setRelation('student', $student);
                        $attendances->push($tempAttendance);
                    }
                }
            }
        } else {
            $attendances = $event->attendances;
        }

        return $attendances;
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats(Collection $attendances, ?Session $activeSession, Collection $allStudentNumbers): array
    {
        if ($activeSession) {
            $sessionAttendances = $attendances->where('session_id', $activeSession->getKey());
            $totalStudents = $allStudentNumbers->count();
            $checkedIn = $sessionAttendances->filter(fn($a) => $a->time_in !== null)->count();
            $absent = $totalStudents - $checkedIn;
            $attendancePercentage = $totalStudents > 0 ? round(($checkedIn / $totalStudents) * 100) : 0;
        } else {
            $totalStudents = $allStudentNumbers->count();
            $checkedIn = $attendances->filter(fn($a) => $a->time_in !== null)->count();
            $absent = $totalStudents - $checkedIn;
            $attendancePercentage = $totalStudents > 0 ? round(($checkedIn / $totalStudents) * 100) : 0;
        }

        return [$totalStudents, $checkedIn, $absent, $attendancePercentage];
    }

    /**
     * Delete an attendance record and all records for that student in this event
     */
    public function deleteAttendance(int $attendanceId)
    {
        try {
            $attendance = Attendance::findOrFail($attendanceId);
            $studentNumber = $attendance->snumber;
            $eventId = $attendance->event_id;
            
            // Delete ALL attendance records for this student in this event (across all sessions)
            Attendance::where('event_id', $eventId)
                ->where('snumber', $studentNumber)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student removed from attendance successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the scan attendance page
     */
    public function scanAttendance(int $eventId)
    {
        $event = Event::with('user')->findOrFail($eventId);
        $students = Student::whereIn('snumber', 
            $event->attendances()
                ->select('snumber')
                ->distinct()
                ->pluck('snumber')
        )->get();

        return view('scan-attendance', [
            'event' => $event,
            'students' => $students
        ]);
    }

    /**
     * Get student details by student number
     */
    public function getStudentByNumber(string $studentId)
    {
        try {
            $student = Student::where('snumber', $studentId)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'student' => [
                    'snumber' => $student->snumber,
                    'name' => $student->name,
                    'section' => $student->section,
                    'program' => $student->program,
                    'rfid' => $student->rfid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
    }

    /**
     * Get student details by RFID
     */
    public function getStudentByRFID(string $rfid)
    {
        try {
            $student = Student::where('rfid', $rfid)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'student' => [
                    'snumber' => $student->snumber,
                    'name' => $student->name,
                    'section' => $student->section,
                    'program' => $student->program,
                    'rfid' => $student->rfid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
    }

    /**
     * Create a new student
     */
    public function createStudent(Request $request)
    {
        try {
            $validated = $request->validate([
                'snumber' => 'required|string|min:1',
                'name' => 'required|string|max:255',
                'section' => 'required|string|max:50',
                'program' => 'nullable|string|max:100',
                'rfid' => 'nullable|string|max:100'
            ]);

            $exists = Student::where('snumber', $validated['snumber'])->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student with this ID already exists'
                ], 422);
            }

            $student = Student::create([
                'snumber' => $validated['snumber'],
                'name' => $validated['name'],
                'section' => $validated['section'],
                'program' => $validated['program'] ?? null,
                'rfid' => $validated['rfid'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'student' => [
                    'snumber' => $student->snumber,
                    'name' => $student->name,
                    'section' => $student->section,
                    'program' => $student->program,
                    'rfid' => $student->rfid
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Student creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register an existing student to an event (create attendance records for all sessions)
     */
    public function registerStudentToEvent(Request $request, int $eventId)
    {
        try {
            $validated = $request->validate([
                'snumber' => 'required|string|exists:tbl_students,snumber',
            ]);

            // Find the student
            $student = Student::where('snumber', $validated['snumber'])->firstOrFail();

            // Find the event
            $event = Event::findOrFail($eventId);

            // Get all sessions for this event
            $sessions = Session::where('event_id', $eventId)->get();

            if ($sessions->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sessions found for this event'
                ], 422);
            }

            // Check if student is already registered for this event
            $existingAttendance = Attendance::where('event_id', $eventId)
                ->where('snumber', $student->snumber)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already registered for this event'
                ], 422);
            }

            // Create attendance records for all sessions
            /** @var \App\Models\Session $session */
            foreach ($sessions as $session) {
                Attendance::create([
                    'event_id' => $eventId,
                    'session_id' => $session->getKey(),
                    'snumber' => $student->snumber,
                    'time_in' => null,
                    'time_out' => null,
                    'status' => 'absent',
                    'cycles_data' => json_encode([]),
                    'total_duration_minutes' => 0,
                    'last_scan_time' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Student {$student->name} successfully registered for this event",
                'student' => [
                    'snumber' => $student->snumber,
                    'name' => $student->name,
                    'section' => $student->section,
                    'rfid' => $student->rfid
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Student registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error registering student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance data for scanning page
     */
    public function getAttendanceData(int $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            
            // Get all sessions for this event
            $sessions = Session::where('event_id', $eventId)
                ->orderBy('day_number')
                ->orderBy('session_number')
                ->get();

            // Get all attendance records with student details
            $attendanceService = new AttendanceService();
            $attendanceData = Attendance::where('event_id', $eventId)
                ->with(['student' => function($query) {
                    $query->select('snumber', 'name', 'section', 'program', 'rfid');
                }])
                ->get()
                ->map(function($attendance) use ($sessions, $attendanceService) {
                    $student = $attendance->student;
                    if (!$student) {
                        return null; // Skip if student doesn't exist
                    }
                    
                    $session = $sessions->firstWhere('id', $attendance->session_id);
                    $cyclesData = [];
                    try {
                        $cyclesData = AttendanceService::getCycles($attendance);
                    } catch (\Exception $e) {
                        Log::warning('Failed to decode cycles_data for attendance ' . $attendance->id);
                    }
                    
                    return [
                        'id' => $attendance->id,
                        'student_id' => $attendance->snumber,
                        'student_name' => $student->name ?? 'Unknown',
                        'student_program' => $student->program ?? null,
                        'student_rfid' => $student->rfid ?? null,
                        'section' => $student->section ?? 'N/A',
                        'session_id' => $attendance->session_id,
                        'day_num' => $session->day_number ?? 0,
                        'session_number' => $session->session_number ?? 0,
                        'time_in' => $attendance->time_in,
                        'time_out' => $attendance->time_out,
                        'status' => $attendance->status,
                        'total_duration_minutes' => $attendance->total_duration_minutes,
                        'formatted_duration' => $attendanceService->formatDuration($attendance->total_duration_minutes ?? 0),
                        'cycles_count' => count($cyclesData),
                        'cycles_data' => $cyclesData,
                    ];
                })
                ->filter() // Remove null values
                ->values();

            // Format sessions for dropdown
            $sessionsFormatted = $sessions->map(function(Session $session) {
                return [
                    'id' => $session->getKey(),
                    'day_number' => $session->day_number,
                    'session_number' => $session->session_number,
                    'status' => $session->status,
                ];
            });

            return response()->json([
                'success' => true,
                'attendanceData' => $attendanceData,
                'sessions' => $sessionsFormatted,
            ]);
        } catch (\Exception $e) {
            Log::error('getAttendanceData error: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End the current active session
     */
    public function endCurrentSession(Request $request, int $eventId)
    {
        try {
            // Find the currently active session
            $activeSession = Session::where('event_id', $eventId)
                ->where('status', 'active')
                ->firstOrFail();

            // Get all students who are currently marked "present" in this session
            /** @var \Illuminate\Support\Collection<int, Attendance> $presentStudents */
            $presentStudents = Attendance::where('session_id', $activeSession->getKey())
                ->where('status', 'present')
                ->get();

            // Use AttendanceService to time out each student
            $attendanceService = new AttendanceService();
            foreach ($presentStudents as $attendance) {
                $attendanceService->recordScan($attendance, 'time_out');
            }

            // Update the session to completed with end time, preserving user_id
            $activeSession->update([
                'status' => 'completed',
                'end_time' => now(),
                'user_id' => $activeSession->user_id, // Preserve the user_id
            ]);

            // Check if this is the last session being ended for this event
            $upcomingSessions = Session::where('event_id', $eventId)
                ->where('status', 'upcoming')
                ->count();

            // If no more upcoming sessions, update event end_date to today
            if ($upcomingSessions === 0) {
                $event = Event::findOrFail($eventId);
                $event->update([
                    'end_date' => now()->toDateString()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Session ended successfully and all present students have been timed out!',
                'session' => $activeSession,
                'isLastSession' => $upcomingSessions === 0
            ]);
        } catch (\Exception $e) {
            Log::error('End session error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No active session found or error ending session'
            ], 404);
        }
    }

    /**
     * Start the next upcoming session
     */
    public function startNextSession(Request $request, int $eventId)
    {
        try {
            // Debug: Log authentication info
            Log::info('[DEBUG] startNextSession called', [
                'eventId' => $eventId,
                'userId' => Auth::id(),
                'userCheck' => Auth::check() ? 'authenticated' : 'not authenticated',
                'user' => Auth::user() ? Auth::user()->email : 'no user'
            ]);

            // Track the personnel starting the session
            $event = Event::findOrFail($eventId);

            // Check if the event date is in the future
            $eventStartDate = \Carbon\Carbon::parse($event->start_date)->startOfDay();
            $today = \Carbon\Carbon::today();

            if ($today->isBefore($eventStartDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start sessions before the event start date (' . $eventStartDate->format('M d, Y') . ')'
                ], 403);
            }

            if (!$event->user_id) {
                // Only set user_id if not already set by the event creator
                $event->update(['user_id' => Auth::id()]);
            }

            // End current active session if one exists
            $currentActive = Session::where('event_id', $eventId)
                ->where('status', 'active')
                ->first();

            if ($currentActive) {
                // Get all students who are currently marked "present" in this session
                /** @var \Illuminate\Support\Collection<int, Attendance> $presentStudents */
                $presentStudents = Attendance::where('session_id', $currentActive->getKey())
                    ->where('status', 'present')
                    ->get();

                // Use AttendanceService to time out each student
                $attendanceService = new AttendanceService();
                foreach ($presentStudents as $attendance) {
                    $attendanceService->recordScan($attendance, 'time_out');
                }

                // Mark current session as completed
                $currentActive->update([
                    'status' => 'completed',
                    'end_time' => now(),
                ]);
            }

            // Find the next upcoming session
            $nextSession = Session::where('event_id', $eventId)
                ->where('status', 'upcoming')
                ->orderBy('day_number')
                ->orderBy('session_number')
                ->firstOrFail();

            // Debug: Log before update
            Log::info('[DEBUG] Session before update:', [
                'session_id' => $nextSession->getKey(),
                'current_user_id' => $nextSession->user_id,
                'Auth::id()' => Auth::id()
            ]);

            // Update the session to active with start time and user_id
            $updateData = [
                'status' => 'active',
                'start_time' => now(),
                'user_id' => Auth::id(), // Track who started this session
            ];
            
            Log::info('[DEBUG] Attempting to update session with:', $updateData);
            
            $nextSession->update($updateData);
            
            // Refresh and log after update
            $nextSession->refresh();
            Log::info('[DEBUG] Session after update:', [
                'session_id' => $nextSession->getKey(),
                'status' => $nextSession->status,
                'start_time' => $nextSession->start_time,
                'user_id' => $nextSession->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session started successfully!',
                'session' => $nextSession
            ]);
        } catch (\Exception $e) {
            Log::error('Start session error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No upcoming sessions found'
            ], 404);
        }
    }

    /**
     * Get the user managing the active session for an event
     */
    public function getActiveSessionManager(int $eventId)
    {
        try {
            // Find the active session for this event
            $activeSession = Session::where('event_id', $eventId)
                ->where('status', 'active')
                ->first();

            if (!$activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found'
                ], 200);
            }

            if (!$activeSession->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session has no manager assigned'
                ], 200);
            }

            // Get the user who started the session
            $user = $activeSession->user;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager user not found'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'manager' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get session manager error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching session manager'
            ], 500);
        }
    }

    /**
     * Mark attendance as present for a student (with cycle tracking)
     */
    public function markAttendancePresent(Request $request, int $eventId)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'student_id' => 'nullable|string|max:50',
                'rfid' => 'nullable|string|max:100',
            ]);

            // Trim whitespace
            $validated['student_id'] = trim($validated['student_id'] ?? '');
            $validated['rfid'] = trim($validated['rfid'] ?? '');

            // Ensure at least one identifier is provided
            if (empty($validated['student_id']) && empty($validated['rfid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter Student ID or scan RFID tag'
                ], 422);
            }

            // Find the event
            $event = Event::findOrFail($eventId);

            // Find the active session
            $activeSession = Session::where('event_id', $eventId)
                ->where('status', 'active')
                ->first();

            if (!$activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found. Please start a session first.'
                ], 422);
            }

            // Find student by ID or RFID
            $student = null;
            $searchMethod = null;

            if (!empty($validated['student_id'])) {
                $student = Student::where('snumber', $validated['student_id'])->first();
                $searchMethod = 'Student ID';
            } elseif (!empty($validated['rfid'])) {
                $student = Student::where('rfid', $validated['rfid'])->first();
                $searchMethod = 'RFID';
            }

            if (!$student) {
                $identifier = $validated['student_id'] ?: $validated['rfid'];
                return response()->json([
                    'success' => false,
                    'message' => "Student not found with $searchMethod: $identifier"
                ], 404);
            }

            // Check if student is registered for this event
            $eventAttendance = Attendance::where('event_id', $eventId)
                ->where('snumber', $student->snumber)
                ->first();

            if (!$eventAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not registered for this event'
                ], 422);
            }

            // Get or create attendance record for this session
            $attendance = Attendance::firstOrCreate(
                [
                    'event_id' => $eventId,
                    'session_id' => $activeSession->getKey(),
                    'snumber' => $student->snumber,
                ],
                [
                    'cycles_data' => json_encode([]),
                    'status' => 'absent',
                ]
            );

            // Use AttendanceService to handle cycle tracking
            $attendanceService = new AttendanceService();

            // Check if student can scan (30-second cooldown)
            if (!$attendanceService->canScan($attendance)) {
                $cooldownRemaining = $attendanceService->getCooldownRemaining($attendance);
                
                // DEBUG: Log the exact timestamps and calculation
                $now = \Carbon\Carbon::now();
                $lastScan = $attendance->last_scan_time instanceof \Carbon\Carbon 
                    ? $attendance->last_scan_time 
                    : \Carbon\Carbon::parse($attendance->last_scan_time);
                
                $secondsElapsed = $now->getTimestamp() - $lastScan->getTimestamp();
                
                $debug = [
                    'last_scan_time_raw' => $attendance->last_scan_time,
                    'last_scan_timestamp' => $lastScan->getTimestamp(),
                    'current_timestamp' => $now->getTimestamp(),
                    'seconds_elapsed' => $secondsElapsed,
                    'cooldown_remaining_calculated' => $cooldownRemaining
                ];
                
                return response()->json([
                    'success' => false,
                    'cooldown_active' => true,
                    'cooldown_remaining' => $cooldownRemaining,
                    'message' => "Scan too fast! Please wait {$cooldownRemaining}s before scanning again.",
                    'student' => [
                        'name' => $student->name,
                        'snumber' => $student->snumber,
                    ],
                    '_debug' => $debug
                ], 429); // 429 Too Many Requests
            }

            // If this is the first time_in, record it
            if (is_null($attendance->time_in) && (empty($attendance->cycles_data) || count(AttendanceService::getCycles($attendance)) === 0)) {
                // recordScan saves to database and returns updated object
                $attendance = $attendanceService->recordScan($attendance, 'time_in');
                
                return response()->json([
                    'success' => true,
                    'message' => "✓ Marked {$student->name} as present",
                    'student' => [
                        'name' => $student->name,
                        'snumber' => $student->snumber,
                        'section' => $student->section,
                    ],
                    'attendance' => [
                        'id' => $attendance->id,
                        'time_in' => $attendance->time_in,
                        'session_id' => $activeSession->getKey(),
                    ]
                ]);
            }

            // Refresh attendance record from database to get latest time_out status
            $attendance->refresh();

            // If already timed out, cannot scan again
            // Check the cycles_data for time_out in the last cycle
            $cyclesData = AttendanceService::getCycles($attendance);
            $hasTimedOut = false;
            
            if (!empty($cyclesData)) {
                // Check if the last cycle has a time_out
                $lastCycle = end($cyclesData);
                if ($lastCycle && isset($lastCycle['time_out']) && !is_null($lastCycle['time_out'])) {
                    $hasTimedOut = true;
                }
            }
            
            if ($hasTimedOut) {
                return response()->json([
                    'success' => false,
                    'message' => "Student already timed out for this session and cannot be timed in again",
                    'student' => [
                        'name' => $student->name,
                        'snumber' => $student->snumber,
                    ],
                ], 422);
            }

            // If already scanned and cooldown expired, handle based on toggle setting
            // Check if auto-timeout is disabled (require_action_prompts = 0 means auto-timeout is ON)
            if ($event->require_action_prompts === 0) {
                // Auto-timeout is ON - automatically time out the student
                $attendance = $attendanceService->recordScan($attendance, 'time_out');
                
                return response()->json([
                    'success' => true,
                    'show_modal' => false,
                    'auto_timed_out' => true,
                    'message' => "✓ Timed Out {$student->name}",
                    'student' => [
                        'name' => $student->name,
                        'snumber' => $student->snumber,
                        'section' => $student->section,
                    ],
                    'attendance' => [
                        'attendance_id' => $attendance->id,
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'time_out' => $attendance->time_out,
                    ]
                ]);
            } else {
                // Auto-timeout is OFF - show modal for user to choose action
                return response()->json([
                    'success' => true,
                    'show_modal' => true,
                    'message' => 'Student has already scanned. Choose action:',
                    'student' => [
                        'name' => $student->name,
                        'snumber' => $student->snumber,
                        'section' => $student->section,
                    ],
                    'attendance' => [
                        'attendance_id' => $attendance->id,
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                    ]
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Mark attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a new student to an existing event
     */
    public function addStudentToEvent(Request $request, int $eventId)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'student_name' => 'required|string|max:255',
                'student_number' => 'required|string|max:50',
                'section' => 'required|string|max:50',
                'program' => 'nullable|string|max:100',
                'rfid' => 'nullable|string|max:100',
            ]);

            // Find the event
            $event = Event::findOrFail($eventId);

            // Get all sessions for this event
            $sessions = Session::where('event_id', $eventId)->get();

            if ($sessions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sessions found for this event'
                ], 422);
            }

            // Create or find the student
            $student = Student::firstOrCreate(
                ['snumber' => $validated['student_number']],
                [
                    'name' => $validated['student_name'],
                    'section' => $validated['section'],
                    'program' => $validated['program'] ?? null,
                    'rfid' => $validated['rfid'] ?? null,
                ]
            );

            // Check if student is already registered for this event
            $existingAttendance = Attendance::where('event_id', $eventId)
                ->where('snumber', $student->snumber)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already registered for this event'
                ], 422);
            }

            // Create attendance records for each session
            /** @var Session $session */
            foreach ($sessions as $session) {
                Attendance::create([
                    'event_id' => $eventId,
                    'session_id' => $session->getKey(),
                    'snumber' => $student->snumber,
                    'time_in' => null,
                    'time_out' => null,
                    'status' => 'absent',
                    'cycles_data' => json_encode([]),
                    'total_duration_minutes' => 0,
                    'last_scan_time' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Student added successfully to the event',
                'student' => [
                    'snumber' => $student->snumber,
                    'name' => $student->name,
                    'section' => $student->section,
                    'program' => $student->program,
                    'rfid' => $student->rfid,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Add student to event error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch add multiple students to an event
     */
    public function batchAddStudentsToEvent(Request $request, int $eventId)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'students' => 'required|array|min:1|max:500', // Limit batch size to prevent memory issues
                'students.*.student_name' => 'required|string|max:255',
                'students.*.student_number' => 'required|string|max:50',
                'students.*.section' => 'required|string|max:50',
                'students.*.program' => 'nullable|string|max:100',
                'students.*.rfid' => 'nullable|string|max:100',
            ]);

            // Find the event
            $event = Event::findOrFail($eventId);

            // Get all sessions for this event
            $sessions = Session::where('event_id', $eventId)->get();

            if ($sessions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sessions found for this event'
                ], 422);
            }

            $results = [
                'total_processed' => 0,
                'added' => 0,
                'skipped_duplicates' => 0,
                'errors' => [],
                'students' => []
            ];

            // Use database transaction for atomicity
            DB::transaction(function () use ($validated, $eventId, $sessions, &$results) {
                $studentsData = $validated['students'];

                // Get all student numbers to check for existing registrations
                $studentNumbers = collect($studentsData)->pluck('student_number')->unique()->toArray();

                // Check which students are already registered for this event
                $existingAttendances = Attendance::where('event_id', $eventId)
                    ->whereIn('snumber', $studentNumbers)
                    ->pluck('snumber')
                    ->toArray();

                // Get existing students from database
                $existingStudents = Student::whereIn('snumber', $studentNumbers)
                    ->pluck('snumber')
                    ->toArray();

                $newStudents = [];
                $attendanceRecords = [];
                $processedStudentNumbers = [];

                foreach ($studentsData as $index => $studentData) {
                    $results['total_processed']++;

                    try {
                        $studentNumber = $studentData['student_number'];

                        // Skip duplicate rows in the uploaded batch
                        if (in_array($studentNumber, $processedStudentNumbers, true)) {
                            $results['skipped_duplicates']++;
                            continue;
                        }

                        $processedStudentNumbers[] = $studentNumber;

                        // Skip if already registered for this event
                        if (in_array($studentNumber, $existingAttendances, true)) {
                            $results['skipped_duplicates']++;
                            continue;
                        }

                        // Check if student exists, if not, prepare for creation
                        if (!in_array($studentNumber, $existingStudents)) {
                            $newStudents[] = [
                                'snumber' => $studentNumber,
                                'name' => $studentData['student_name'],
                                'section' => $studentData['section'],
                                'program' => $studentData['program'] ?? null,
                                'rfid' => $studentData['rfid'] ?? null,
                            ];
                        }

                        // Prepare attendance records for all sessions
                        /** @var Session $session */
                        foreach ($sessions as $session) {
                            $attendanceRecords[] = [
                                'event_id' => $eventId,
                                'session_id' => $session->getKey(),
                                'snumber' => $studentNumber,
                                'time_in' => null,
                                'time_out' => null,
                                'status' => 'absent',
                                'cycles_data' => json_encode([]),
                                'total_duration_minutes' => 0,
                                'last_scan_time' => null,
                            ];
                        }

                        $results['added']++;
                        $results['students'][] = [
                            'snumber' => $studentNumber,
                            'name' => $studentData['student_name'],
                            'section' => $studentData['section'],
                            'program' => $studentData['program'],
                            'rfid' => $studentData['rfid'],
                        ];

                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'index' => $index,
                            'student_number' => $studentData['student_number'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ];
                    }
                }

                // Bulk insert new students
                if (!empty($newStudents)) {
                    Student::insert($newStudents);
                }

                // Bulk insert attendance records
                if (!empty($attendanceRecords)) {
                    Attendance::insert($attendanceRecords);
                }
            });

            $message = "Batch import completed: {$results['added']} students added";
            if ($results['skipped_duplicates'] > 0) {
                $message .= ", {$results['skipped_duplicates']} duplicates skipped";
            }
            if (!empty($results['errors'])) {
                $message .= ", " . count($results['errors']) . " errors";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Batch add students to event error: ' . $e->getMessage());
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process attendance cycle actions (break, time_out, return_from_break)
     */
    public function processAttendanceAction(Request $request, int $eventId)
    {
        try {
            $validated = $request->validate([
                'attendance_id' => 'required|exists:tbl_attendance,id',
                'action' => 'required|in:break,time_out,return_from_break',
            ]);

            $attendance = Attendance::findOrFail($validated['attendance_id']);
            $action = $validated['action'];

            // Verify the attendance record belongs to the event
            if ($attendance->event_id != $eventId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record does not belong to this event'
                ], 401);
            }

            // Use AttendanceService to process the action
            $attendanceService = new AttendanceService();
            $attendanceService->recordScan($attendance, $action);
            
            // Reset cooldown after action is processed
            // This allows next scan to start a fresh 30-second cooldown window
            $attendanceService->resetCooldown($attendance);

            // Get the updated attendance record
            $attendance->refresh();
            $student = $attendance->student;

            return response()->json([
                'success' => true,
                'message' => "Action '{$action}' recorded for {$student->name}",
                'student' => [
                    'name' => $student->name,
                    'snumber' => $student->snumber,
                ],
                'attendance' => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'total_duration_minutes' => $attendance->total_duration_minutes,
                    'formatted_duration' => $attendanceService->formatDuration($attendance->total_duration_minutes ?? 0),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Process attendance action error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive an event - moves to archive and schedules auto-delete after 15 days
     */
    public function archiveEvent(int $eventId)
    {
        try {
            // Additional validation
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please log in'
                ], 401);
            }

            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Admin privileges required'
                ], 403);
            }

            $event = Event::findOrFail($eventId);

            // Log before update
            Log::info("Attempting to archive event ID {$eventId}", [
                'event_name' => $event->e_name,
                'current_status' => $event->status
            ]);

            // Update event status to archived
            $event->update([
                'e_status' => 'archived',
                'archived_at' => now(),
                'archived_delete_at' => now()->addDays(15),
            ]);

            Log::info("Event archived successfully: ID {$eventId} - will be auto-deleted on {$event->archived_delete_at}");

            return response()->json([
                'success' => true,
                'message' => 'Event archived successfully. It will be permanently deleted after 15 days.'
            ]);
        } catch (\Exception $e) {
            $userEmail = Auth::check() ? (Auth::user()->email ?? 'unknown') : 'guest';
            Log::error('Archive event error: ' . $e->getMessage(), [
                'eventId' => $eventId,
                'user' => $userEmail,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unarchive an event
     */
    public function unarchiveEvent(int $eventId)
    {
        try {
            // Admin authorization check
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please log in'
                ], 401);
            }

            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Admin privileges required'
                ], 403);
            }

            $event = Event::findOrFail($eventId);

            if ($event->status !== 'archived') {
                return response()->json([
                    'success' => false,
                    'message' => 'Event is not archived'
                ], 400);
            }

            // Restore event from archive
            $event->update([
                'status' => 'completed',
                'archived_at' => null,
                'archived_delete_at' => null,
                'e_status' => 'completed'
            ]);

            Log::info("Event unarchived: ID {$eventId}");

            return response()->json([
                'success' => true,
                'message' => 'Event restored from archive successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Unarchive event error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to unarchive event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived events for admin view
     */
    public function getArchivedEvents()
    {
        try {
            $archivedEvents = Event::where('e_status', 'archived')
                ->orderBy('archived_at', 'desc')
                ->get()
                ->map(function(Event $event) {
                    $daysRemaining = now()->diffInDays($event->archived_delete_at);
                    // Count unique students from attendance records for this event
                    $totalStudents = Attendance::where('event_id', $event->getKey())
                        ->distinct('snumber')
                        ->count('snumber');
                    
                    return [
                        'e_id' => $event->getKey(),
                        'e_name' => $event->e_name,
                        'archived_at' => $event->archived_at,
                        'archived_delete_at' => $event->archived_delete_at,
                        'daysRemaining' => max(0, $daysRemaining),
                        'start_date' => $event->start_date,
                        'start_time' => $event->start_time,
                        'totalStudents' => $totalStudents
                    ];
                });

            return response()->json([
                'success' => true,
                'archivedEvents' => $archivedEvents
            ]);
        } catch (\Exception $e) {
            Log::error('Get archived events error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch archived events'
            ], 500);
        }
    }
    public function deleteEvent(int $eventId)
    {
        try {
            // Admin authorization check
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please log in'
                ], 401);
            }

            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Admin privileges required'
                ], 403);
            }

            $event = Event::findOrFail($eventId);

            // Log the deletion for audit purposes
            $adminEmail = Auth::check() ? (Auth::user()->email ?? 'unknown') : 'unknown';
            Log::warning("PERMANENT DELETION: Event '{$event->e_name}' (ID: {$eventId}) and all related data will be deleted by admin " . $adminEmail);

            // Find all students with attendance records in this event (before deleting attendance)
            $studentsInEvent = Attendance::whereHas('session', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })->pluck('snumber')->unique()->toArray();

            // Delete all attendance records for this event
            $deletedAttendances = Attendance::whereHas('session', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })->delete();

            Log::info("Deleted {$deletedAttendances} attendance records for event {$eventId}");

            // Delete all sessions for this event
            $deletedSessions = Session::where('event_id', $eventId)->delete();

            Log::info("Deleted {$deletedSessions} sessions for event {$eventId}");

            // Delete students that have no attendance records in any other event
            $deletedStudents = 0;
            foreach ($studentsInEvent as $snumber) {
                $hasOtherAttendance = Attendance::where('snumber', $snumber)->exists();
                if (!$hasOtherAttendance) {
                    Student::where('snumber', $snumber)->delete();
                    $deletedStudents++;
                }
            }

            if ($deletedStudents > 0) {
                Log::info("Deleted {$deletedStudents} students with no other event attendance for event {$eventId}");
            }

            // Delete the event itself
            $event->delete();

            Log::warning("Event {$eventId} permanently deleted along with all associated data");

            return response()->json([
                'success' => true,
                'message' => 'Event and all related data have been permanently deleted.'
            ]);
        } catch (\Exception $e) {
            $userEmail = Auth::check() ? (Auth::user()->email ?? 'unknown') : 'guest';
            Log::error('Delete event error: ' . $e->getMessage(), [
                'eventId' => $eventId,
                'user' => $userEmail
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }
}

