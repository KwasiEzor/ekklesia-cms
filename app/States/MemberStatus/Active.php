<?php

namespace App\States\MemberStatus;

class Active extends MemberStatus
{
    public static string $name = 'active';

    public function label(): string
    {
        return __('members.statuses.active');
    }

    public function color(): string
    {
        return 'success';
    }
}
