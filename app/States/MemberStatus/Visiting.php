<?php

namespace App\States\MemberStatus;

class Visiting extends MemberStatus
{
    public static string $name = 'visiting';

    public function label(): string
    {
        return __('members.statuses.visiting');
    }

    public function color(): string
    {
        return 'info';
    }
}
