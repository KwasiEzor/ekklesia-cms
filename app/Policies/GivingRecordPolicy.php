<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GivingRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class GivingRecordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GivingRecord');
    }

    public function view(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('View:GivingRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GivingRecord');
    }

    public function update(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('Update:GivingRecord');
    }

    public function delete(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('Delete:GivingRecord');
    }

    public function restore(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('Restore:GivingRecord');
    }

    public function forceDelete(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('ForceDelete:GivingRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GivingRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GivingRecord');
    }

    public function replicate(AuthUser $authUser, GivingRecord $givingRecord): bool
    {
        return $authUser->can('Replicate:GivingRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GivingRecord');
    }

}