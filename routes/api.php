<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceLedgerController;
use App\Http\Controllers\ExcuseRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes that require authentication must be inside the group below.
| The auth.php file handles /login and /logout.
|
*/


require __DIR__.'/auth.php';


Route::middleware(['auth:sanctum', 'check.expiry'])->group(function () {

    Route::get('/me', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
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
});
