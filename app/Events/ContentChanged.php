<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $contentType;

    public int $contentId;

    public string $contentTitle;

    public string $tenantId;

    public function __construct(
        Model $model,
        public string $action,
        public ?string $changedBy = null,
    ) {
        $this->contentType = class_basename($model);
        $this->contentId = $model->getKey();
        $this->contentTitle = $model->title ?? $model->name ?? "#{$model->getKey()}";
        $this->tenantId = $model->tenant_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'content.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'content_type' => $this->contentType,
            'action' => $this->action,
            'content_id' => $this->contentId,
            'content_title' => $this->contentTitle,
            'changed_by' => $this->changedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
