<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReadingPlanRequest;
use App\Http\Requests\Api\V1\UpdateReadingPlanRequest;
use App\Http\Resources\MemberReadingProgressResource;
use App\Http\Resources\ReadingPlanCollection;
use App\Http\Resources\ReadingPlanResource;
use App\Models\MemberReadingProgress;
use App\Models\ReadingPlan;
use App\Models\ReadingPlanDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    /**
     * List reading plans.
     *
     * Retrieve a paginated list of Bible reading plans.
     *
     * @queryParam active boolean Filter for active plans. Example: true
     * @queryParam per_page int Number of items per page. Example: 15
     */
    public function index(Request $request): ReadingPlanCollection
    {
        $this->authorize('viewAny', ReadingPlan::class);

        $query = ReadingPlan::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $plans = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return new ReadingPlanCollection($plans);
    }

    /**
     * Create reading plan.
     *
     * Create a new Bible reading plan with optional daily entries.
     */
    public function store(StoreReadingPlanRequest $request): JsonResponse
    {
        $this->authorize('create', ReadingPlan::class);

        $plan = ReadingPlan::create([
            ...$request->safe()->except('days'),
            'tenant_id' => tenant('id'),
        ]);

        if ($request->has('days')) {
            foreach ($request->input('days') as $day) {
                $plan->days()->create([
                    ...$day,
                    'tenant_id' => tenant('id'),
                ]);
            }
        }

        return (new ReadingPlanResource($plan->fresh()->load('days')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Retrieve reading plan.
     *
     * Get details of a specific reading plan with its daily entries.
     */
    public function show(ReadingPlan $readingPlan): ReadingPlanResource
    {
        $this->authorize('view', $readingPlan);

        return new ReadingPlanResource($readingPlan->load('days'));
    }

    /**
     * Update reading plan.
     *
     * Update an existing reading plan.
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): ReadingPlanResource
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update($request->validated());

        return new ReadingPlanResource($readingPlan->fresh()->load('days'));
    }

    /**
     * Delete reading plan.
     *
     * Remove a reading plan from the system.
     */
    public function destroy(ReadingPlan $readingPlan): JsonResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return response()->json(null, 204);
    }

    /**
     * Subscribe member to plan.
     *
     * Subscribe a member to a reading plan.
     */
    public function subscribe(Request $request, ReadingPlan $readingPlan): JsonResponse
    {
        $this->authorize('view', $readingPlan);

        $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ]);

        $existing = MemberReadingProgress::where('member_id', $request->input('member_id'))
            ->where('reading_plan_id', $readingPlan->id)
            ->whereNull('reading_plan_day_id')
            ->first();

        if ($existing) {
            return response()->json(['message' => __('reading_plans.already_subscribed')], 409);
        }

        $progress = MemberReadingProgress::create([
            'member_id' => $request->input('member_id'),
            'reading_plan_id' => $readingPlan->id,
            'started_at' => now(),
            'tenant_id' => tenant('id'),
        ]);

        return (new MemberReadingProgressResource($progress))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Complete a reading day.
     *
     * Mark a specific day as completed for a member's reading plan.
     */
    public function completeDay(Request $request, ReadingPlan $readingPlan, ReadingPlanDay $day): JsonResponse
    {
        $this->authorize('view', $readingPlan);

        $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ]);

        $memberId = $request->input('member_id');

        $existing = MemberReadingProgress::where('member_id', $memberId)
            ->where('reading_plan_id', $readingPlan->id)
            ->where('reading_plan_day_id', $day->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => __('reading_plans.day_already_completed')], 409);
        }

        // Get the subscription record to update streak
        $subscription = MemberReadingProgress::where('member_id', $memberId)
            ->where('reading_plan_id', $readingPlan->id)
            ->whereNull('reading_plan_day_id')
            ->first();

        // Calculate streak
        $lastCompleted = MemberReadingProgress::where('member_id', $memberId)
            ->where('reading_plan_id', $readingPlan->id)
            ->whereNotNull('reading_plan_day_id')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->first();

        $currentStreak = 1;
        $gracePeriodDays = $readingPlan->grace_period_days;

        if ($lastCompleted && $lastCompleted->completed_at->diffInDays(now()) <= ($gracePeriodDays + 1)) {
            $currentStreak = ($subscription?->current_streak ?? 0) + 1;
        }

        $longestStreak = max($currentStreak, $subscription?->longest_streak ?? 0);

        // Update subscription streak
        if ($subscription) {
            $subscription->update([
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
            ]);
        }

        $progress = MemberReadingProgress::create([
            'member_id' => $memberId,
            'reading_plan_id' => $readingPlan->id,
            'reading_plan_day_id' => $day->id,
            'completed_at' => now(),
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'started_at' => $subscription?->started_at ?? now(),
            'tenant_id' => tenant('id'),
        ]);

        return response()->json([
            'progress' => new MemberReadingProgressResource($progress),
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
        ]);
    }

    /**
     * Get member progress.
     *
     * Get a member's progress for a specific reading plan.
     *
     * @queryParam member_id int required The member ID. Example: 1
     */
    public function progress(Request $request, ReadingPlan $readingPlan): JsonResponse
    {
        $this->authorize('view', $readingPlan);

        $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ]);

        $memberId = $request->input('member_id');

        $subscription = MemberReadingProgress::where('member_id', $memberId)
            ->where('reading_plan_id', $readingPlan->id)
            ->whereNull('reading_plan_day_id')
            ->first();

        $completedDays = MemberReadingProgress::where('member_id', $memberId)
            ->where('reading_plan_id', $readingPlan->id)
            ->whereNotNull('reading_plan_day_id')
            ->whereNotNull('completed_at')
            ->with('readingPlanDay')
            ->get();

        $totalDays = $readingPlan->days()->count();

        return response()->json([
            'subscribed' => $subscription !== null,
            'started_at' => $subscription?->started_at?->toDateString(),
            'current_streak' => $subscription?->current_streak ?? 0,
            'longest_streak' => $subscription?->longest_streak ?? 0,
            'completed_days' => $completedDays->count(),
            'total_days' => $totalDays,
            'progress_percent' => $totalDays > 0 ? round(($completedDays->count() / $totalDays) * 100, 1) : 0,
            'completed_day_ids' => $completedDays->pluck('reading_plan_day_id'),
        ]);
    }
}
