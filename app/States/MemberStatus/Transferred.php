<?php

namespace App\States\MemberStatus;

class Transferred extends MemberStatus
{
    public static string $name = 'transferred';

    public function label(): string
    {
        return __('members.statuses.transferred');
    }

    public function color(): string
    {
        return 'warning';
    }
}
