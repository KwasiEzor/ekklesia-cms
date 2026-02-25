<?php

namespace App\Notifications;

use App\Events\ContentChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ContentChanged $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actionLabels = [
            'created' => __('common.created_action', [], 'fr') ?: 'créé',
            'updated' => __('common.updated_action', [], 'fr') ?: 'modifié',
            'deleted' => __('common.deleted_action', [], 'fr') ?: 'supprimé',
        ];

        $action = $actionLabels[$this->event->action] ?? $this->event->action;
        $who = $this->event->changedBy ?? 'Système';

        return [
            'title' => "{$this->event->contentType} {$action}",
            'body' => "{$who} a {$action} « {$this->event->contentTitle} »",
            'content_type' => $this->event->contentType,
            'content_id' => $this->event->contentId,
            'action' => $this->event->action,
        ];
    }
}
