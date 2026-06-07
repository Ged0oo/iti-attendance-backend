<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SessionAttendanceController;

// ========================================
// PUBLIC ROUTES (No Token Required)
// ========================================
Route::post('/login', function (Request $request) {
    // A raw, temporary login bypass just for your testing
    $user = User::where('email', $request->email)->firstOrFail();
    return response()->json([
        'token' => $user->createToken('test-token')->plainTextToken
    ]);
});


// ========================================
// M4 — Attendance Management APIs
// ========================================
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Student Scanning Endpoint
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
    
    // Instructor/TA Management Endpoints
    Route::get('/sessions/{session}/attendance', [SessionAttendanceController::class, 'index']);
    Route::post('/sessions/{session}/close', [SessionAttendanceController::class, 'close']);

    // QR Code Generation Endpoint
    Route::get('/sessions/{session}/qr-code', [\App\Http\Controllers\Api\QrCodeController::class, 'generate']);

    // NFC Hardware Flow
    Route::post('/nfc/register', [\App\Http\Controllers\Api\NfcAttendanceController::class, 'register']);
    Route::post('/nfc/lost', [\App\Http\Controllers\Api\NfcAttendanceController::class, 'reportLost']);
    
    // The endpoint the NFC reader hardware hits
    Route::post('/nfc/scan', [\App\Http\Controllers\Api\NfcAttendanceController::class, 'scan']);
});


// Courses, grade components and billing
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('courses', \App\Http\Controllers\Api\CourseController::class);
    Route::apiResource('grade-components', \App\Http\Controllers\Api\GradeComponentController::class);
});