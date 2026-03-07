<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Generate permissions first via Shield
        // This ensures all resource-based permissions exist in the DB
        $this->command->info('Generating permissions via Shield...');
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Iterate through UserRole enum to create roles and assign permissions
        foreach (UserRole::cases() as $roleEnum) {
            $this->command->info("Processing role: {$roleEnum->value}...");

            // Super Admin is always global (team_id is null)
            // Other roles are also created globally here as templates,
            // but can be assigned per tenant.
            setPermissionsTeamId(null);

            $role = Role::firstOrCreate([
                'name' => $roleEnum->value,
                'guard_name' => 'web',
                'team_id' => null,
            ]);

            if ($roleEnum === UserRole::SUPER_ADMIN) {
                // Super Admin gets everything
                $role->syncPermissions(Permission::all());
                $this->command->info("  - Assigned all " . Permission::count() . " permissions to Super Admin.");
            } else {
                // Other roles get their specific subset defined in the Enum
                $permissions = $roleEnum->getPermissions();
                
                // Verify permissions exist before syncing (to avoid errors)
                $existingPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
                $missingPermissions = array_diff($permissions, $existingPermissions);
                
                if (!empty($missingPermissions)) {
                    $this->command->warn("  - Missing permissions for {$roleEnum->value}: " . implode(', ', $missingPermissions));
                }

                $role->syncPermissions($existingPermissions);
                $this->command->info("  - Assigned " . count($existingPermissions) . " permissions to {$roleEnum->value}.");
            }
        }

        $this->command->info('Roles and permissions seeding completed successfully.');
    }
}
