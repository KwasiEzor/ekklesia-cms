<?php

namespace App\Listeners;

use App\Events\ContentChanged;
use App\Models\User;
use App\Notifications\ContentChangedNotification;

class NotifyTenantAdmins
{
    public function handle(ContentChanged $event): void
    {
        $users = User::where('tenant_id', $event->tenantId)->get();

        // Don't notify the user who made the change
        $recipients = $users->filter(function (User $user) use ($event) {
            return $user->name !== $event->changedBy;
        });

        foreach ($recipients as $user) {
            $user->notify(new ContentChangedNotification($event));
        }
    }
}
