<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AssignmentSubmissionController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\GradeOverrideController;
use App\Http\Controllers\Api\SessionAttendanceController;
use App\Http\Controllers\Api\StudentGradeCardController;
use App\Http\Controllers\Api\StudentNoteController;
use App\Http\Controllers\Api\StudentTagController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

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


// Anyone signed in can browse the schedule (read only)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('courses', \App\Http\Controllers\Api\CourseController::class)->only(['index', 'show']);
    Route::apiResource('grade-components', \App\Http\Controllers\Api\GradeComponentController::class)->only(['index', 'show']);
    Route::apiResource('instructors', \App\Http\Controllers\Api\InstructorController::class)->only(['index', 'show']);
    Route::apiResource('lab-groups', \App\Http\Controllers\Api\LabGroupController::class)->only(['index', 'show']);
    Route::apiResource('engagements', \App\Http\Controllers\Api\EngagementController::class)->only(['index', 'show']);
    Route::apiResource('sessions', \App\Http\Controllers\Api\SessionController::class)->only(['index', 'show']);
});

// Only the track admin (or branch manager) configures the cohort
Route::middleware(['auth:sanctum', \Spatie\Permission\Middleware\RoleMiddleware::using('track_admin|branch_manager')])->group(function () {
    Route::apiResource('courses', \App\Http\Controllers\Api\CourseController::class)->except(['index', 'show']);
    Route::apiResource('grade-components', \App\Http\Controllers\Api\GradeComponentController::class)->except(['index', 'show']);
    Route::apiResource('instructors', \App\Http\Controllers\Api\InstructorController::class)->except(['index', 'show']);
    Route::apiResource('lab-groups', \App\Http\Controllers\Api\LabGroupController::class)->except(['index', 'show']);
    Route::apiResource('engagements', \App\Http\Controllers\Api\EngagementController::class)->except(['index', 'show']);
    Route::apiResource('sessions', \App\Http\Controllers\Api\SessionController::class)->except(['index', 'show']);
    Route::post('engagements/{engagement}/sessions/generate', [\App\Http\Controllers\Api\SessionController::class, 'generate']);
    Route::patch('sessions/{session}/deliver', [\App\Http\Controllers\Api\SessionController::class, 'deliver']);
});

// Billing rollup is for the branch manager only
Route::middleware(['auth:sanctum', \Spatie\Permission\Middleware\RoleMiddleware::using('branch_manager')])->group(function () {
    Route::get('billing', [\App\Http\Controllers\Api\BillingController::class, 'index']);
    Route::post('billing/calculate', [\App\Http\Controllers\Api\BillingController::class, 'calculate']);
    Route::get('billing/{billingRecord}', [\App\Http\Controllers\Api\BillingController::class, 'show']);
    Route::patch('billing/{billingRecord}/finalize', [\App\Http\Controllers\Api\BillingController::class, 'finalize']);
});

// Grading read/write endpoints for instructors and admins.
Route::middleware(['auth:sanctum', 'check.expiry', RoleMiddleware::using('instructor|track_admin|branch_manager')])->group(function () {
    Route::apiResource('grades', GradeController::class)->only(['index', 'store', 'show', 'update']);
    Route::apiResource('assignment-submissions', AssignmentSubmissionController::class)
        ->parameters(['assignment-submissions' => 'assignmentSubmission'])
        ->only(['index', 'show']);
    Route::apiResource('student-tags', StudentTagController::class)
        ->parameters(['student-tags' => 'studentTag'])
        ->only(['index', 'store', 'show']);
    Route::apiResource('student-notes', StudentNoteController::class)
        ->parameters(['student-notes' => 'studentNote'])
        ->only(['index', 'store', 'show']);
});

// Students submit their own assignment work.
Route::middleware(['auth:sanctum', 'check.expiry', RoleMiddleware::using('student|track_admin|branch_manager')])->group(function () {
    Route::post('assignment-submissions', [AssignmentSubmissionController::class, 'store']);
});

// Grade cards are used by student portal and staff review screens.
Route::middleware(['auth:sanctum', 'check.expiry', RoleMiddleware::using('student|instructor|track_admin|branch_manager')])->group(function () {
    Route::get('students/{student}/grade-card', StudentGradeCardController::class);
});

// Grade destructive/admin actions.
Route::middleware(['auth:sanctum', 'check.expiry', RoleMiddleware::using('track_admin|branch_manager')])->group(function () {
    Route::patch('grades/{grade}/override', GradeOverrideController::class);
    Route::delete('grades/{grade}', [GradeController::class, 'destroy']);
    Route::delete('assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'destroy']);
    Route::delete('student-tags/{studentTag}', [StudentTagController::class, 'destroy']);
    Route::delete('student-notes/{studentNote}', [StudentNoteController::class, 'destroy']);
});
