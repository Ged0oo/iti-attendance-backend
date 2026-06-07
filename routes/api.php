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
// M4 — Attendance & QR (Token Required)
// ========================================
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Student Scanning Endpoint
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
    
    // Instructor/TA Management Endpoints
    Route::get('/sessions/{session}/attendance', [SessionAttendanceController::class, 'index']);
    Route::post('/sessions/{session}/close', [SessionAttendanceController::class, 'close']);

    // QR Code Generation Endpoint
    Route::get('/sessions/{session}/qr-code', [\App\Http\Controllers\Api\QrCodeController::class, 'generate']);
    
});