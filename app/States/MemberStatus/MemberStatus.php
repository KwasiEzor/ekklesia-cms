<?php

namespace App\States\MemberStatus;

use Spatie\ModelStates\Attributes\DefaultState;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

#[DefaultState(Active::class)]
abstract class MemberStatus extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Active::class)
            ->allowTransition(Active::class, Inactive::class)
            ->allowTransition(Active::class, Transferred::class)
            ->allowTransition(Inactive::class, Active::class)
            ->allowTransition(Visiting::class, Active::class)
            ->allowTransition(Visiting::class, Inactive::class);
    }
}
