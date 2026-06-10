<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\ExcuseRequest;
use App\Http\Requests\StoreExcuseRequest;
use App\Http\Requests\ReviewExcuseRequest;
use App\Http\Resources\ExcuseRequestResource;
use App\Services\AttendanceLedgerService;
use App\Enums\ExcuseStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExcuseRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole(UserRole::STUDENT->value)) {
            $student = $user->student;
            $excuses = $student ? $student->excuseRequests()->latest()->get() : collect();
        } else {
            // staff see the pending queue
            $excuses = ExcuseRequest::where('status', ExcuseStatus::Requested)->latest()->get();
        }

        return ExcuseRequestResource::collection($excuses);
    }

    public function store(StoreExcuseRequest $request)
    {
        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'Only students can submit excuse requests.');
        }

        // the record being excused has to belong to this student
        $record = AttendanceRecord::findOrFail($request->attendance_record_id);
        if ($record->student_id !== $student->id) {
            abort(403, 'You can only submit an excuse for your own attendance.');
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
            return response()->json(['message' => 'Excuse has already been reviewed'], 422);
        }

        $excuse->status = $request->decision === 'approved' ? ExcuseStatus::Approved : ExcuseStatus::Rejected;
        $excuse->reviewer_id = Auth::id();
        $excuse->reviewed_at = now();
        $excuse->save();

        if ($excuse->status === ExcuseStatus::Approved) {
            $ledgerService->applyExcuseApproval($excuse);
        }

        return new ExcuseRequestResource($excuse);
    }
}
