<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ReadingPlanDay
 */
class ReadingPlanDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_number' => $this->day_number,
            'title' => $this->title,
            'passage_reference' => $this->passage_reference,
            'passage_text' => $this->passage_text,
            'reflection' => $this->reflection,
        ];
    }
}
