<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create
        {name : The tenant (church) display name}
        {slug : URL-safe identifier used for subdomain}
        {admin-email : Email for the initial admin user}
        {--admin-name= : Name for the admin user (defaults to "Admin")}
        {--admin-password= : Password for the admin user (will be prompted if not provided)}
        {--domain= : Custom domain to associate with the tenant}';

    protected $description = 'Create a new tenant with an initial admin user';

    public function handle(): int
    {
        $name = $this->argument('name');
        $slug = $this->argument('slug');
        $adminEmail = $this->argument('admin-email');
        $adminName = $this->option('admin-name') ?? 'Admin';
        $domain = $this->option('domain');

        // Validate inputs
        $validator = Validator::make([
            'name' => $name,
            'slug' => $slug,
            'email' => $adminEmail,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:tenants,id'],
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Get or prompt for password
        $password = $this->option('admin-password');
        if (! $password) {
            $password = $this->secret('Enter admin password (min 8 characters)');
        }

        $passwordValidator = Validator::make(['password' => $password], [
            'password' => ['required', Password::min(8)],
        ]);

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Create tenant
        $tenant = Tenant::create([
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
        ]);

        $this->info("Tenant created: {$tenant->id} ({$name})");

        // Create domain if provided
        if ($domain) {
            $tenant->domains()->create(['domain' => $domain]);
            $this->info("Domain registered: {$domain}");
        }

        // Create default subdomain
        $subdomain = "{$slug}.".parse_url((string) config('app.url'), PHP_URL_HOST);
        $tenant->domains()->create(['domain' => $subdomain]);
        $this->info("Subdomain registered: {$subdomain}");

        // Initialize tenancy to create the admin user in the right context
        tenancy()->initialize($tenant);

        $user = User::create([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make($password),
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created: {$user->email}");

        $this->newLine();
        $this->components->info('Tenant setup complete!');
        $this->table(
            ['Property', 'Value'],
            [
                ['Tenant ID', $tenant->id],
                ['Name', $name],
                ['Subdomain', $subdomain],
                ['Admin Email', $adminEmail],
            ]
        );

        return self::SUCCESS;
    }
}
