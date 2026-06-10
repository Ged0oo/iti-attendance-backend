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
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceLedgerController;
use App\Http\Controllers\ExcuseRequestController;
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

    // M5 — only staff manage the roster
    Route::get('/cohorts/{cohort}/students', [StudentController::class, 'index'])->middleware('role:track_admin,branch_manager');
    Route::post('/students', [StudentController::class, 'store'])->middleware('role:track_admin,branch_manager');
    Route::get('/students/at-risk', [StudentController::class, 'atRisk'])->middleware('role:track_admin,branch_manager');
    Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('role:track_admin,branch_manager');
    Route::patch('/students/{student}/lab-group', [StudentController::class, 'assignLabGroup'])->middleware('role:track_admin,branch_manager');

    // a student may read their own profile and ledger (controller scopes it)
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::get('/students/{student}/ledger', [AttendanceLedgerController::class, 'show']);
    Route::get('/students/{student}/ledger/entries', [AttendanceLedgerController::class, 'entries']);

    // excuses
    Route::get('/excuse-requests', [ExcuseRequestController::class, 'index']);
    Route::get('/excuse-requests/{excuse}', [ExcuseRequestController::class, 'show']);
    Route::post('/excuse-requests', [ExcuseRequestController::class, 'store'])->middleware('role:student');
    Route::patch('/excuse-requests/{excuse}/review', [ExcuseRequestController::class, 'review'])->middleware('role:track_admin,branch_manager');

    // user provisioning (also checked top down by the UserPolicy)
    Route::get('/users', [UserController::class, 'index'])->middleware('role:track_admin,branch_manager');
    Route::post('/users', [UserController::class, 'store'])->middleware('role:track_admin,branch_manager');
});

Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {

    // Student Scanning Endpoint
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);

    // Instructor/TA Management Endpoints
    Route::get('/sessions/{session}/attendance', [SessionAttendanceController::class, 'index'])
        ->middleware('role:instructor,track_admin,branch_manager');
    Route::post('/sessions/{session}/close', [SessionAttendanceController::class, 'close'])
        ->middleware('role:instructor,track_admin,branch_manager');

    // QR Code Generation
    Route::get('/sessions/{session}/qr-code', [\App\Http\Controllers\Api\QrCodeController::class, 'generate'])
        ->middleware('role:instructor,track_admin,branch_manager');

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

// Grading read/write endpoints for instructors and admins.
Route::middleware(['auth:sanctum', 'check.expiry', RoleMiddleware::using('instructor|track_admin|branch_manager')])->group(function () {
    Route::apiResource('grades', GradeController::class)->only(['index', 'store', 'show', 'update']);
    Route::apiResource('assignment-submissions', AssignmentSubmissionController::class)
        ->parameters(['assignment-submissions' => 'assignmentSubmission'])
        ->only(['index', 'show']);
    Route::apiResource('student-tags', StudentTagController::class)
        ->parameters(['student-tags' => 'studentTag'])
        ->only(['index', 'store', 'show', 'update']);
    Route::apiResource('student-notes', StudentNoteController::class)
        ->parameters(['student-notes' => 'studentNote'])
        ->only(['index', 'store', 'show', 'update']);
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

// ========================================
// Tracks, Cohorts & Announcements
// ========================================
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
