<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
});
