<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocsSyncCommand extends Command
{
    protected $signature = 'app:docs-sync {--tests : Run tests to update test count}';

    protected $description = 'Synchronize root-level documentation with the VitePress site';

    public function handle(): int
    {
        $this->info('Starting Documentation Sync...');

        // 1. Export API Specification
        $this->comment('Exporting API Specification (Scramble)...');
        $this->call('scramble:export', ['--path' => 'api.json']);

        $publicDir = base_path('docs/public');
        if (! File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }

        File::copy(base_path('api.json'), $publicDir.'/api.json');
        $this->info('  - API Spec synced to docs/public/api.json');

        // 2. Sync Development Log
        $this->comment('Syncing Development Log (BUILD_PROGRESS.md)...');
        $progressContent = File::get(base_path('BUILD_PROGRESS.md'));

        // Clean up the progress report for VitePress (e.g., adjust headings)
        $docLog = "# Development Log\n\n".
                  "> This is a real-time record of the project's construction, synced from the root `BUILD_PROGRESS.md`.\n\n".
                  $progressContent;

        File::put(base_path('docs/guide/dev-log.md'), $docLog);
        $this->info('  - Dev Log synced to docs/guide/dev-log.md');

        // 3. Update Test Count in Homepage
        $testCount = $this->getTestCount();
        if ($testCount) {
            $this->comment("Updating Homepage with test count: {$testCount}...");
            $indexFile = base_path('docs/index.md');
            $indexContent = File::get($indexFile);

            // Replace "X tests," pattern
            $updatedIndex = preg_replace(
                '/([0-9]+) tests,/',
                "{$testCount} tests,",
                $indexContent
            );

            File::put($indexFile, $updatedIndex);
            $this->info("  - docs/index.md updated with {$testCount} tests.");
        }

        // 4. Update Roadmap status from Phase 6
        $this->comment('Verifying Roadmap status...');
        $this->syncRoadmapStatus();

        $this->info('Documentation sync completed successfully!');

        return self::SUCCESS;
    }

    protected function getTestCount(): ?int
    {
        if ($this->option('tests')) {
            $this->info('Running tests to get accurate count...');
            $output = shell_exec('vendor/bin/pest --compact');
            if (preg_match('/([0-9]+) passed/', $output, $matches)) {
                return (int) $matches[1];
            }
        }

        // Try reading from common log locations or just use the current one
        $indexContent = File::get(base_path('docs/index.md'));
        if (preg_match('/([0-9]+) tests,/', $indexContent, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function syncRoadmapStatus(): void
    {
        $roadmapFile = base_path('docs/guide/roadmap.md');
        $roadmapContent = File::get($roadmapFile);

        // Ensure Phase 6 is marked as complete if it's not
        if (Str::contains($roadmapContent, '## Phase 6 — Premium Modules (Current)')) {
            $this->warn('Phase 6 still marked as "Current" in roadmap. Updating...');
            $updated = str_replace(
                '## Phase 6 — Premium Modules (Current)',
                '## Phase 6 — Premium Modules :white_check_mark:',
                $roadmapContent
            );
            $updated = str_replace(
                '> Status: **In Progress**',
                '> Status: **Complete** (March 2026)',
                $updated
            );
            File::put($roadmapFile, $updated);
        }
    }
}
