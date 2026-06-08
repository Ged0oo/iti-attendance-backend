<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingRecordResource;
use App\Models\BillingRecord;
use App\Models\Engagement;
use App\Models\Instructor;
use App\Models\Session;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BillingController extends Controller
{
    // Rollup the branch manager looks at: every record plus the internal vs external split.
    public function index(Request $request): JsonResponse
    {
        $records = BillingRecord::query()
            ->when($request->integer('cohort_id'), fn ($q, $id) => $q->where('cohort_id', $id))
            ->get();

        $external = (float) $records->where('compensation_type', 'external')->sum('total_amount');
        $internal = (float) $records->where('compensation_type', 'internal')->sum('total_amount');

        return response()->json([
            'data' => BillingRecordResource::collection($records),
            'summary' => [
                'external_total' => round($external, 2),
                'internal_total' => round($internal, 2),
                'grand_total' => round($external + $internal, 2),
            ],
        ]);
    }

    // Build the billing records for a cohort from the sessions that were actually delivered.
    public function calculate(Request $request, BillingService $billing): JsonResponse
    {
        $data = $request->validate([
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            'billing_period_start' => ['required', 'date'],
            'billing_period_end' => ['required', 'date', 'after_or_equal:billing_period_start'],
        ]);

        // anyone with an engagement in this cohort gets a billing line
        $instructorUserIds = Engagement::where('cohort_id', $data['cohort_id'])
            ->distinct()
            ->pluck('instructor_id');

        $records = collect();

        foreach ($instructorUserIds as $userId) {
            $sessions = Session::whereHas('engagement', function ($q) use ($data, $userId) {
                $q->where('cohort_id', $data['cohort_id'])->where('instructor_id', $userId);
            })->whereBetween('date', [$data['billing_period_start'], $data['billing_period_end']]);

            $scheduledHours = (float) (clone $sessions)->sum('scheduled_hours');
            $deliveredHours = (float) (clone $sessions)->where('is_delivered', true)->sum('scheduled_hours');

            $profile = Instructor::where('user_id', $userId)->first();
            $type = $profile->compensation_type ?? BillingService::TYPE_EXTERNAL;
            $rate = (float) ($profile->hourly_rate ?? 0);
            $salary = (float) ($profile->fixed_salary ?? 0);

            $records->push(BillingRecord::updateOrCreate(
                [
                    'cohort_id' => $data['cohort_id'],
                    'user_id' => $userId,
                    'billing_period_start' => $data['billing_period_start'],
                    'billing_period_end' => $data['billing_period_end'],
                ],
                [
                    'compensation_type' => $type,
                    'scheduled_hours' => $scheduledHours,
                    'delivered_hours' => $deliveredHours,
                    'hourly_rate' => $rate,
                    'fixed_salary' => $salary,
                    'total_amount' => $billing->total($type, $deliveredHours, $rate, $salary),
                    // status is left alone so a finalized record is not knocked back to draft
                ]
            ));
        }

        return response()->json(
            ['data' => BillingRecordResource::collection($records)],
            Response::HTTP_CREATED
        );
    }

    public function show(BillingRecord $billingRecord): BillingRecordResource
    {
        return new BillingRecordResource($billingRecord);
    }

    public function finalize(BillingRecord $billingRecord): BillingRecordResource
    {
        $billingRecord->update(['status' => 'finalized']);

        return new BillingRecordResource($billingRecord);
    }
}
