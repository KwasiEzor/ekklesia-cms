<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReadingPlan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReadingPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReadingPlan');
    }

    public function view(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('View:ReadingPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReadingPlan');
    }

    public function update(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('Update:ReadingPlan');
    }

    public function delete(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('Delete:ReadingPlan');
    }

    public function restore(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('Restore:ReadingPlan');
    }

    public function forceDelete(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('ForceDelete:ReadingPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReadingPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReadingPlan');
    }

    public function replicate(AuthUser $authUser, ReadingPlan $readingPlan): bool
    {
        return $authUser->can('Replicate:ReadingPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReadingPlan');
    }
}
