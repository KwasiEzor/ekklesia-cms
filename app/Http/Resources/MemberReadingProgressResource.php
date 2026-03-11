<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\MemberReadingProgress
 */
class MemberReadingProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'reading_plan_id' => $this->reading_plan_id,
            'reading_plan_day_id' => $this->reading_plan_day_id,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'current_streak' => $this->current_streak,
            'longest_streak' => $this->longest_streak,
            'started_at' => $this->started_at->toDateString(),
            'reading_plan' => new ReadingPlanResource($this->whenLoaded('readingPlan')),
            'day' => new ReadingPlanDayResource($this->whenLoaded('readingPlanDay')),
        ];
    }
}
