<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\TenantContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BenchmarkOctane extends Command
{
    protected $signature = 'app:benchmark 
        {--tenants=100 : Number of concurrent tenants to simulate}
        {--requests=10 : Requests per tenant}
        {--seed : Whether to seed each tenant with data}
        {--url=http://ekklesia-cms.test : The base URL to hit}';

    protected $description = 'Simulate concurrent activity across many tenants to test Octane stability';

    public function handle(): int
    {
        $tenantCount = (int) $this->option('tenants');
        $requestsPerTenant = (int) $this->option('requests');
        $baseUrl = rtrim($this->option('url'), '/');
        $host = parse_url($baseUrl, PHP_URL_HOST) ?: 'ekklesia-cms.test';

        $this->info("Starting benchmark for {$tenantCount} tenants...");
        $this->info("Base URL: {$baseUrl}");

        $tenants = $this->prepareTenants($tenantCount);

        if ($this->option('seed')) {
            $this->seedTenants($tenants);
        }

        $this->info('Simulating activity... Total requests: '.($tenantCount * $requestsPerTenant));

        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);

        $successCount = 0;
        $failCount = 0;

        $bar = $this->output->createProgressBar($tenantCount * $requestsPerTenant);
        $bar->start();

        // We use pooling for concurrency
        for ($i = 0; $i < $requestsPerTenant; $i++) {
            $responses = Http::pool(function ($pool) use ($tenants, $host, $baseUrl) {
                $calls = [];
                foreach ($tenants as $tenant) {
                    $tenantDomain = "{$tenant->id}.{$host}";
                    // We hit the /give endpoint as it is public and exercises Livewire/Models
                    $calls[] = $pool->withHeaders(['Host' => $tenantDomain])
                        ->get("{$baseUrl}/give");
                }

                return $calls;
            });

            foreach ($responses as $response) {
                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $duration = $endTime - $startTime;
        $avgTime = $duration / ($tenantCount * $requestsPerTenant);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', $successCount + $failCount],
                ['Successes', "<info>{$successCount}</info>"],
                ['Failures', $failCount > 0 ? "<error>{$failCount}</error>" : '0'],
                ['Total Duration', number_format($duration, 2).'s'],
                ['Avg Request Time', number_format($avgTime * 1000, 2).'ms'],
                ['Peak Script Memory', number_format(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB'],
            ]
        );

        $this->info('Check Octane/FrankenPHP memory usage on host for real-time monitoring.');

        return self::SUCCESS;
    }

    protected function prepareTenants(int $count): array
    {
        $tenants = [];
        $this->comment("Preparing {$count} benchmark tenants...");

        for ($i = 1; $i <= $count; $i++) {
            $id = "bench-{$i}";
            $tenant = Tenant::find($id);

            if (! $tenant) {
                $tenant = Tenant::create([
                    'id' => $id,
                    'name' => "Benchmark Church {$i}",
                    'slug' => $id,
                ]);

                $host = parse_url($this->option('url'), PHP_URL_HOST) ?: 'ekklesia-cms.test';
                $tenant->domains()->create(['domain' => "{$id}.{$host}"]);
            }

            $tenants[] = $tenant;
        }

        return $tenants;
    }

    protected function seedTenants(array $tenants): void
    {
        $this->comment('Seeding tenants (this may take a while)...');
        $bar = $this->output->createProgressBar(count($tenants));
        $bar->start();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            $seeder = new TenantContentSeeder;
            $seeder->run();
            tenancy()->end();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
