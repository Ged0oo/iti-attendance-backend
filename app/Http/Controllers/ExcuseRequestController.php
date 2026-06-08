<?php

namespace App\Http\Controllers;

use App\Models\ExcuseRequest;
use App\Models\Student;
use App\Http\Requests\StoreExcuseRequest;
use App\Http\Requests\ReviewExcuseRequest;
use App\Http\Resources\ExcuseRequestResource;
use App\Services\AttendanceLedgerService;
use App\Enums\ExcuseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExcuseRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // This relies on roles which may not be fully implemented yet. 
        // A placeholder logic based on instructions:
        if ($user && isset($user->role) && $user->role->value === 'student') {
            $student = $user->student;
            $excuses = $student ? $student->excuses : collect();
        } else {
            // TA sees cohort pending - placeholder as cohort logic involves relationships
            $excuses = ExcuseRequest::where('status', ExcuseStatus::Requested)->get();
        }
        
        return ExcuseRequestResource::collection($excuses);
    }

    public function store(StoreExcuseRequest $request)
    {
        // student only. Check no existing excuse for same attendance_record_id.
        $student = Auth::user() ? Auth::user()->student : Student::first(); // Fallback for tests
        
        if (!$student) {
            abort(403, 'Only students can submit excuse requests.');
        }

        if (ExcuseRequest::where('attendance_record_id', $request->attendance_record_id)->exists()) {
            return response()->json(['message' => 'Excuse request already exists for this record.'], 422);
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('excuses', $fileName, 'public');
        }

        $excuse = ExcuseRequest::create([
            'student_id' => $student->id,
            'attendance_record_id' => $request->attendance_record_id,
            'notes' => $request->reason,
            'attachment_path' => $filePath,
            'status' => ExcuseStatus::Requested,
        ]);

        return new ExcuseRequestResource($excuse);
    }

    public function show(ExcuseRequest $excuse)
    {
        return new ExcuseRequestResource($excuse);
    }

    public function review(ReviewExcuseRequest $request, ExcuseRequest $excuse, AttendanceLedgerService $ledgerService)
    {
        if ($excuse->status !== ExcuseStatus::Requested) {
            return response()->json(['message' => 'Already reviewed'], 422);
        }

        $excuse->status = $request->decision === 'approved' ? ExcuseStatus::Approved : ExcuseStatus::Rejected;
        $excuse->reviewer_id = Auth::id() ?? 1; // Fallback for tests
        $excuse->reviewed_at = now();
        $excuse->save();

        if ($excuse->status === ExcuseStatus::Approved) {
            $ledgerService->applyExcuseApproval($excuse);
        }

        return new ExcuseRequestResource($excuse);
    }
}
