<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnniversaryNotification extends Notification
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
        $years = $this->member->years_married;
        $name = $this->member->full_name;

        return [
            'title' => __('birthdays.anniversary_notification_title'),
            'body' => $years
                ? __('birthdays.anniversary_notification_body_with_years', ['name' => $name, 'years' => $years])
                : __('birthdays.anniversary_notification_body', ['name' => $name]),
            'member_id' => $this->member->id,
            'type' => 'anniversary',
        ];
    }
}
