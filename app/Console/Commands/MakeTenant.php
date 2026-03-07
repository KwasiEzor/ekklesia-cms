<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeTenant extends Command
{
    protected $signature = 'app:make-tenant';

    protected $description = 'Interactively create a new tenant with a domain and a Super Admin user';

    public function handle(): void
    {
        $this->info('--- Ekklesia CMS Tenant Creator ---');

        // 1. Get Inputs
        $name = $this->ask('Enter the Church/Tenant Name', 'Demo Church');
        $email = $this->ask('Enter the Super Admin Email', 'admin@' . Str::slug($name) . '.localhost');
        $password = $this->secret('Enter the Super Admin Password (default: password)') ?? 'password';

        // 2. Automated Slug Generation
        $slug = Str::slug($name);
        
        // Handle slug collisions automatically
        if (Tenant::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . Str::random(4);
        }

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");
            return;
        }

        $this->info("Creating tenant: {$name} ({$slug})...");

        // 3. Create Tenant & Domain
        $tenant = Tenant::create([
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
        ]);

        $tenant->domains()->create([
            'domain' => "{$slug}.localhost",
        ]);

        // 4. Create User
        $user = User::create([
            'name' => "Admin for {$name}",
            'email' => $email,
            'password' => Hash::make($password),
            'tenant_id' => $tenant->id,
        ]);

        // 5. Initialize Tenancy context
        tenancy()->initialize($tenant);
        
        // 6. Ensure super_admin role exists and has all permissions
        setPermissionsTeamId(null);
        $superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web', 'team_id' => null]);
        $superAdminRole->syncPermissions(\App\Models\Permission::all());

        // 7. Assign role to user FOR THIS TENANT
        setPermissionsTeamId($tenant->id);
        $user->assignRole(UserRole::SUPER_ADMIN->value);

        // Verify assignment immediately
        if (!$user->hasRole(UserRole::SUPER_ADMIN->value)) {
            $this->error('Failed to assign super_admin role.');
            return;
        }

        $this->info('Successfully created tenant and user!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Tenant Name', $name],
                ['Domain URL', "http://{$slug}.localhost:8000/admin"],
                ['Login Email', $email],
                ['Login Password', '(hidden)'],
            ]
        );

        $this->warn('Note: Ensure your local environment handles .localhost domains.');
    }
}
