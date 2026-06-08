<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\LedgerEntryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceLedgerController extends Controller
{
    public function show(Student $student)
    {
        // student own or TA (middleware/policy assumed)
        $ledger = $student->ledger;
        return new LedgerResource($ledger);
    }

    public function entries(Student $student)
    {
        // student own or TA
        $ledger = $student->ledger;
        return LedgerEntryResource::collection($ledger->entries);
    }
}
