<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Per-user private channel (Filament notifications)
Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

// Tenant-scoped content channel — all admins in a tenant receive updates
Broadcast::channel('tenant.{tenantId}', function (User $user, string $tenantId) {
    return $user->tenant_id === $tenantId;
});
