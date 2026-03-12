<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PrayerRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PrayerRequest');
    }

    public function view(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('View:PrayerRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PrayerRequest');
    }

    public function update(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('Update:PrayerRequest');
    }

    public function delete(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('Delete:PrayerRequest');
    }

    public function restore(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('Restore:PrayerRequest');
    }

    public function forceDelete(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('ForceDelete:PrayerRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PrayerRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PrayerRequest');
    }

    public function replicate(AuthUser $authUser, PrayerRequest $prayerRequest): bool
    {
        return $authUser->can('Replicate:PrayerRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PrayerRequest');
    }

}