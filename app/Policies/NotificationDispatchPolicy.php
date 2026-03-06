<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NotificationDispatch;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationDispatchPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NotificationDispatch');
    }

    public function view(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('View:NotificationDispatch');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NotificationDispatch');
    }

    public function update(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('Update:NotificationDispatch');
    }

    public function delete(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('Delete:NotificationDispatch');
    }

    public function restore(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('Restore:NotificationDispatch');
    }

    public function forceDelete(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('ForceDelete:NotificationDispatch');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NotificationDispatch');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NotificationDispatch');
    }

    public function replicate(AuthUser $authUser, NotificationDispatch $notificationDispatch): bool
    {
        return $authUser->can('Replicate:NotificationDispatch');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NotificationDispatch');
    }

}