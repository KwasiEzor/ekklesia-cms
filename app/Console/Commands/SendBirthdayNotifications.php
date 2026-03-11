<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AnniversaryNotification;
use App\Notifications\BirthdayNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendBirthdayNotifications extends Command
{
    protected $signature = 'members:send-birthday-notifications';

    protected $description = 'Send birthday and anniversary notifications to all tenant admins';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $totalBirthdays = 0;
        $totalAnniversaries = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $birthdayMembers = Member::birthdayToday()->get();
            $anniversaryMembers = Member::anniversaryToday()->get();

            if ($birthdayMembers->isEmpty() && $anniversaryMembers->isEmpty()) {
                tenancy()->end();

                continue;
            }

            $admins = User::where('tenant_id', $tenant->id)->get();

            foreach ($birthdayMembers as $member) {
                Notification::send($admins, new BirthdayNotification($member));
                $totalBirthdays++;
            }

            foreach ($anniversaryMembers as $member) {
                Notification::send($admins, new AnniversaryNotification($member));
                $totalAnniversaries++;
            }

            tenancy()->end();
        }

        $this->info("Sent {$totalBirthdays} birthday and {$totalAnniversaries} anniversary notifications.");

        return self::SUCCESS;
    }
}
