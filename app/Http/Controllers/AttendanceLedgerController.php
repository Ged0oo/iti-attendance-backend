<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\LedgerEntryResource;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceLedgerController extends Controller
{
    public function show(Student $student)
    {
        $user = Auth::user();
        if ($user && $user->hasRole(UserRole::STUDENT->value) && $student->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $ledger = $student->ledger;
        return new LedgerResource($ledger);
    }

    public function entries(Student $student)
    {
        $user = Auth::user();
        if ($user && $user->hasRole(UserRole::STUDENT->value) && $student->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $ledger = $student->ledger;
        return LedgerEntryResource::collection($ledger->entries);
    }
}
