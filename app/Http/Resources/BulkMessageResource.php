<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BulkMessage
 */
class BulkMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'channel' => $this->channel,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'recipient_count' => $this->recipient_count,
            'sent_count' => $this->sent_count,
            'failed_count' => $this->failed_count,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
