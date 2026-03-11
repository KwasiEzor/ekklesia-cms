<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BirthdayNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Member $member,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $age = $this->member->age;
        $name = $this->member->full_name;

        return [
            'title' => __('birthdays.notification_title'),
            'body' => $age
                ? __('birthdays.notification_body_with_age', ['name' => $name, 'age' => $age])
                : __('birthdays.notification_body', ['name' => $name]),
            'member_id' => $this->member->id,
            'type' => 'birthday',
        ];
    }
}
