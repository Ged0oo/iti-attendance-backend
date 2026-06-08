<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SessionAttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceLedgerController;
use App\Http\Controllers\ExcuseRequestController;

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

    // M5
    Route::get('/cohorts/{cohort}/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/at-risk', [StudentController::class, 'atRisk']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::patch('/students/{student}/lab-group', [StudentController::class, 'assignLabGroup']);
    Route::get('/students/{student}/ledger', [AttendanceLedgerController::class, 'show']);
    Route::get('/students/{student}/ledger/entries', [AttendanceLedgerController::class, 'entries']);
    Route::get('/excuse-requests', [ExcuseRequestController::class, 'index']);
    Route::post('/excuse-requests', [ExcuseRequestController::class, 'store']);
    Route::get('/excuse-requests/{excuse}', [ExcuseRequestController::class, 'show']);
    Route::patch('/excuse-requests/{excuse}/review', [ExcuseRequestController::class, 'review']);

    Route::post('/users', [UserController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {

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
Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {
    Route::apiResource('courses', \App\Http\Controllers\Api\CourseController::class)->only(['index', 'show']);
    Route::apiResource('grade-components', \App\Http\Controllers\Api\GradeComponentController::class)->only(['index', 'show']);
    Route::apiResource('instructors', \App\Http\Controllers\Api\InstructorController::class)->only(['index', 'show']);
    Route::apiResource('lab-groups', \App\Http\Controllers\Api\LabGroupController::class)->only(['index', 'show']);
    Route::apiResource('engagements', \App\Http\Controllers\Api\EngagementController::class)->only(['index', 'show']);
    Route::apiResource('sessions', \App\Http\Controllers\Api\SessionController::class)->only(['index', 'show']);

    // nested listings (same controllers, scoped by the parent in the url)
    Route::get('cohorts/{cohort}/courses', [\App\Http\Controllers\Api\CourseController::class, 'index']);
    Route::get('courses/{course}/components', [\App\Http\Controllers\Api\GradeComponentController::class, 'index']);
    Route::get('cohorts/{cohort}/lab-groups', [\App\Http\Controllers\Api\LabGroupController::class, 'index']);
    Route::get('cohorts/{cohort}/engagements', [\App\Http\Controllers\Api\EngagementController::class, 'index']);
    Route::get('engagements/{engagement}/sessions', [\App\Http\Controllers\Api\SessionController::class, 'index']);
});

// Only the track admin (or branch manager) configures the cohort
Route::middleware(['auth:sanctum', 'check.expiry', \Spatie\Permission\Middleware\RoleMiddleware::using('track_admin|branch_manager')])->group(function () {
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
Route::middleware(['auth:sanctum', 'check.expiry', \Spatie\Permission\Middleware\RoleMiddleware::using('branch_manager')])->group(function () {
    Route::get('billing', [\App\Http\Controllers\Api\BillingController::class, 'index']);
    Route::post('billing/calculate', [\App\Http\Controllers\Api\BillingController::class, 'calculate']);
    Route::get('billing/{billingRecord}', [\App\Http\Controllers\Api\BillingController::class, 'show']);
    Route::patch('billing/{billingRecord}/finalize', [\App\Http\Controllers\Api\BillingController::class, 'finalize']);
});

// Tracks, Cohorts & Announcements
Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {

    // Tracks
    Route::get('/tracks', [\App\Http\Controllers\TrackController::class, 'index']);
    Route::get('/tracks/{track}', [\App\Http\Controllers\TrackController::class, 'show']);

    Route::middleware('role:branch_manager')->group(function () {
        Route::post('/tracks', [\App\Http\Controllers\TrackController::class, 'store']);
        Route::put('/tracks/{track}', [\App\Http\Controllers\TrackController::class, 'update']);
        Route::delete('/tracks/{track}', [\App\Http\Controllers\TrackController::class, 'destroy']);
    });

    // Track Admins
    Route::get('/tracks/{track}/admins', [\App\Http\Controllers\TrackAdminController::class, 'index']);

    Route::middleware('role:branch_manager')->group(function () {
        Route::post('/tracks/{track}/admins', [\App\Http\Controllers\TrackAdminController::class, 'store']);
        Route::delete('/tracks/{track}/admins/{userId}', [\App\Http\Controllers\TrackAdminController::class, 'destroy']);
    });

    // Cohorts
    Route::get('/cohorts', [\App\Http\Controllers\CohortController::class, 'index']);
    Route::get('/cohorts/{cohort}', [\App\Http\Controllers\CohortController::class, 'show']);

    Route::middleware('role:branch_manager')->group(function () {
        Route::post('/cohorts', [\App\Http\Controllers\CohortController::class, 'store']);
        Route::put('/cohorts/{cohort}', [\App\Http\Controllers\CohortController::class, 'update']);
        Route::delete('/cohorts/{cohort}', [\App\Http\Controllers\CohortController::class, 'destroy']);
    });

    Route::middleware('role:branch_manager,track_admin')->group(function () {
        Route::patch('/cohorts/{cohort}/transition', [\App\Http\Controllers\CohortController::class, 'transition']);
    });

    // Announcements
    Route::get('/cohorts/{cohort}/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index']);
    Route::get('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show']);

    Route::middleware('role:track_admin,instructor')->group(function () {
        Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy']);
    });
});
