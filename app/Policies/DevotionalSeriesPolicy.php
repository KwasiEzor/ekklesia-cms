<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DevotionalSeries;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DevotionalSeriesPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Devotional');
    }

    public function view(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('View:Devotional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Devotional');
    }

    public function update(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('Update:Devotional');
    }

    public function delete(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('Delete:Devotional');
    }

    public function restore(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('Restore:Devotional');
    }

    public function forceDelete(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('ForceDelete:Devotional');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Devotional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Devotional');
    }

    public function replicate(AuthUser $authUser, DevotionalSeries $devotionalSeries): bool
    {
        return $authUser->can('Replicate:Devotional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Devotional');
    }
}
