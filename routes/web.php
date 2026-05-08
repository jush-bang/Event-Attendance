<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/', function () {
	return view('login');
});

Route::post('/login', function (Request $request) {
	$request->validate([
		'email' => 'required|email',
		'password' => 'required',
	]);

	$user = User::where('email', $request->email)->first();
	if ($user && Hash::check($request->password, $user->password)) {
		\Illuminate\Support\Facades\Auth::login($user);
		return redirect('/dashboard');
	}

	return redirect('/')
		->withInput($request->only('email'))
		->withErrors(['login' => 'Invalid email or password.']);
});

Route::post('/logout', function () {
	\Illuminate\Support\Facades\Auth::logout();
	return redirect('/');
})->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/event/{eventId}', [EventController::class, 'showDetail'])->name('event-detail');
Route::post('/event/{eventId}/add-student', [EventController::class, 'addStudentToEvent'])->name('event.add-student');
Route::post('/event/{eventId}/batch-add-students', [EventController::class, 'batchAddStudentsToEvent'])->name('event.batch-add-students');
Route::get('/event/{eventId}/scan-attendance', [EventController::class, 'scanAttendance'])->name('event.scan-attendance');
Route::get('/event/{eventId}/attendance-data', [EventController::class, 'getAttendanceData'])->name('event.attendance-data');
Route::get('/event/{eventId}/attendee-page', [EventController::class, 'getAttendeePage'])->name('event.attendee-page');

// Admin-only routes for event management
Route::middleware('admin')->group(function () {
    Route::post('/event/{eventId}/archive', [EventController::class, 'archiveEvent'])->name('event.archive');
    Route::post('/event/{eventId}/unarchive', [EventController::class, 'unarchiveEvent'])->name('event.unarchive');
    Route::delete('/event/{eventId}', [EventController::class, 'deleteEvent'])->name('event.delete');
    Route::get('/api/archived-events', [EventController::class, 'getArchivedEvents'])->name('archived-events');
});

Route::delete('/attendance/{attendanceId}', [EventController::class, 'deleteAttendance'])->name('delete-attendance');
Route::post('/event/{eventId}/mark-attendance-present', [EventController::class, 'markAttendancePresent'])->name('mark-attendance-present');
Route::post('/event/{eventId}/process-attendance-action', [EventController::class, 'processAttendanceAction'])->name('process-attendance-action');
Route::post('/event/{eventId}/end-session', [EventController::class, 'endCurrentSession'])->name('end-session')->middleware('auth');
Route::post('/event/{eventId}/start-session', [EventController::class, 'startNextSession'])->name('start-session')->middleware('auth');

Route::get('/schedule-event', [EventController::class, 'create'])->name('schedule-event')->middleware('admin');
Route::post('/schedule-event', [EventController::class, 'store'])->name('schedule-event.store')->middleware('admin');
Route::post('/schedule-event/add-student', [EventController::class, 'addStudent'])->name('add-student')->middleware('admin');
Route::delete('/students/{id}', [EventController::class, 'removeStudent'])->name('remove-student');
Route::get('/schedule-event/students/{eventId}', [EventController::class, 'getEventStudents'])->name('get-event-students');

Route::get('/create-account', function() {
	$users = User::all();
	return view('create-account', ['users' => $users]);
})->name('create-account')->middleware('admin');

Route::post('/create-account', function(Request $request) {
	$request->validate([
		'email' => 'required|email|unique:users,email',
		'password' => 'required|min:8',
		'role' => 'required|in:admin,scanner',
	]);

	User::create([
		'name' => explode('@', $request->email)[0],
		'email' => $request->email,
		'password' => Hash::make($request->password),
		'role' => $request->role,
	]);

	return redirect('/create-account')->with('success', 'Account created successfully!');
})->name('create-account.store')->middleware('admin');

Route::put('/accounts/{email}', function(Request $request, $email) {
	$user = User::where('email', $email)->firstOrFail();
	
	if ($request->filled('password')) {
		$user->password = Hash::make($request->password);
		$user->save();
	}
	
	return redirect('/create-account')->with('success', 'Account updated successfully!');
})->middleware('admin');

Route::delete('/accounts/{email}', function($email) {
	$user = User::where('email', $email)->firstOrFail();
	$user->delete();
	
	return redirect('/create-account')->with('success', 'Account deleted successfully!');
})->middleware('admin');

Route::get('/api/student/{studentId}', [EventController::class, 'getStudentByNumber'])->name('api.student');
Route::get('/api/student/lookup-by-rfid/{rfid}', [EventController::class, 'getStudentByRFID'])->name('api.student.lookup-by-rfid');
Route::post('/api/student/create', [EventController::class, 'createStudent'])->name('api.student.create');
Route::post('/api/event/{eventId}/register-student', [EventController::class, 'registerStudentToEvent'])->name('api.register-student');
