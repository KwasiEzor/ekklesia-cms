<?php

namespace App\States\MemberStatus;

class Inactive extends MemberStatus
{
    public static string $name = 'inactive';

    public function label(): string
    {
        return __('members.statuses.inactive');
    }

    public function color(): string
    {
        return 'danger';
    }
}
