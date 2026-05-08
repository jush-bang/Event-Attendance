<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;

Route::post('/login', [AuthController::class, 'login']);

// Student API routes
Route::get('/student/{studentId}', [EventController::class, 'getStudentByNumber']);
Route::get('/student/lookup-by-rfid/{rfid}', [EventController::class, 'getStudentByRFID']);

// Event API routes
Route::get('/event/{eventId}/active-session-manager', [EventController::class, 'getActiveSessionManager']);
