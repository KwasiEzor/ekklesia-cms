<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BulkMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class BulkMessagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BulkMessage');
    }

    public function view(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('View:BulkMessage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BulkMessage');
    }

    public function update(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('Update:BulkMessage');
    }

    public function delete(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('Delete:BulkMessage');
    }

    public function restore(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('Restore:BulkMessage');
    }

    public function forceDelete(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('ForceDelete:BulkMessage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BulkMessage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BulkMessage');
    }

    public function replicate(AuthUser $authUser, BulkMessage $bulkMessage): bool
    {
        return $authUser->can('Replicate:BulkMessage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BulkMessage');
    }

}