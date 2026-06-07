<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SessionAttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {

    // Returns the authenticated user and their role.
    Route::get('/me', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->getRoleNames()->first(),
            'expires_at' => $user->expires_at,
        ]);
    });

    Route::post('/users', [UserController::class, 'store']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    // Student Scanning Endpoint
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);

    // Instructor/TA Management Endpoints
    Route::get('/sessions/{session}/attendance', [SessionAttendanceController::class,
        'index']);
    Route::post('/sessions/{session}/close', [SessionAttendanceController::class,
        'close']);

    // QR Code Generation
    Route::get('/sessions/{session}/qr-code',
        [\App\Http\Controllers\Api\QrCodeController::class, 'generate']);

    // NFC Hardware Flow
    Route::post('/nfc/register',
        [\App\Http\Controllers\Api\NfcAttendanceController::class, 'register']);
    Route::post('/nfc/lost', [\App\Http\Controllers\Api\NfcAttendanceController::class,
        'reportLost']);
    Route::post('/nfc/scan', [\App\Http\Controllers\Api\NfcAttendanceController::class,
        'scan']);
});
