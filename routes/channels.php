<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Per-user private channel (Filament notifications)
Broadcast::channel('App.Models.User.{id}', fn (User $user, $id): bool => (int) $user->id === (int) $id);

// Tenant-scoped content channel — all admins in a tenant receive updates
Broadcast::channel('tenant.{tenantId}', fn (User $user, string $tenantId): bool => $user->tenant_id === $tenantId);
