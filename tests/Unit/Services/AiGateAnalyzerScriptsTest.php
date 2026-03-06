<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

final class AiGateAnalyzerScriptsTest extends TestCase
{
    public function test_rejects_overly_broad_policy_suppressions(): void
    {
        $policy = [
            'allowlist' => [
                [
                    'gate' => '*',
                    'code' => '*',
                    'reason' => 'too broad',
                ],
            ],
            'thresholds' => [
                'test_gaps' => ['min_coverage' => 85],
            ],
        ];

        $policyPath = tempnam(sys_get_temp_dir(), 'policy_');
        self::assertNotFalse($policyPath);

        file_put_contents($policyPath, json_encode($policy, JSON_PRETTY_PRINT));

        [$exitCode, $stdout, $stderr] = $this->runPhpScript(
            $this->projectRoot().'/scripts/ai-gates/analyzers/validate_policy.php',
            ['--policy', $policyPath],
        );

        @unlink($policyPath);

        self::assertSame(1, $exitCode);
        self::assertSame('', $stderr);

        $decoded = json_decode($stdout, true);
        self::assertIsArray($decoded);
        self::assertFalse((bool) ($decoded['valid'] ?? true));
        self::assertStringContainsString('too broad', implode("\n", $decoded['errors'] ?? []));
    }

    public function test_suppresses_findings_using_baseline_fingerprints(): void
    {
        $policyPath = tempnam(sys_get_temp_dir(), 'policy_');
        $baselinePath = tempnam(sys_get_temp_dir(), 'baseline_');
        self::assertNotFalse($policyPath);
        self::assertNotFalse($baselinePath);

        file_put_contents($policyPath, json_encode(['allowlist' => []], JSON_PRETTY_PRINT));

        $finding = [
            'file' => 'app/Services/ExampleService.php',
            'line' => 12,
            'severity' => 'high',
            'code' => 'TENANCY_DB_TABLE',
            'message' => 'DB::table() bypasses model/global-scope tenant guarantees.',
        ];
        $fingerprint = sha1($finding['code'].'|'.$finding['file'].'|'.$finding['line'].'|'.$finding['message']);

        file_put_contents($baselinePath, json_encode([
            'suppress_fingerprints' => [$fingerprint],
        ], JSON_PRETTY_PRINT));

        $input = json_encode(['findings' => [$finding]], JSON_PRETTY_PRINT);
        [$exitCode, $stdout] = $this->runPhpScript(
            $this->projectRoot().'/scripts/ai-gates/analyzers/filter_findings.php',
            ['--gate', 'tenant_isolation', '--policy', $policyPath, '--baseline', $baselinePath],
            $input,
        );

        @unlink($policyPath);
        @unlink($baselinePath);

        self::assertSame(0, $exitCode);

        $decoded = json_decode($stdout, true);
        self::assertIsArray($decoded);
        self::assertSame(0, (int) ($decoded['counts']['active'] ?? -1));
        self::assertSame(1, (int) ($decoded['counts']['suppressed_baseline'] ?? 0));
        self::assertSame($fingerprint, $decoded['suppressed_baseline_findings'][0]['fingerprint'] ?? null);
    }

    public function test_converts_findings_to_sarif_format(): void
    {
        $input = json_encode([
            'active_findings' => [[
                'file' => 'app/Http/Controllers/Api/ExampleController.php',
                'line' => 34,
                'severity' => 'high',
                'code' => 'SEC_MASS_ASSIGN_ALL',
                'message' => 'Avoid passing $request->all() into mass assignment calls.',
            ]],
        ], JSON_PRETTY_PRINT);

        [$exitCode, $stdout] = $this->runPhpScript(
            $this->projectRoot().'/scripts/ai-gates/analyzers/findings_to_sarif.php',
            ['--gate', 'security_review'],
            $input,
        );

        self::assertSame(0, $exitCode);

        $decoded = json_decode($stdout, true);
        self::assertIsArray($decoded);
        self::assertSame('2.1.0', $decoded['version'] ?? null);
        self::assertSame('security_review', $decoded['runs'][0]['automationDetails']['id'] ?? null);
        self::assertSame('SEC_MASS_ASSIGN_ALL', $decoded['runs'][0]['results'][0]['ruleId'] ?? null);
    }

    /**
     * @param  array<int, string>  $args
     * @return array{int, string, string}
     */
    private function runPhpScript(string $scriptPath, array $args = [], ?string $stdin = null): array
    {
        $cmd = 'php '.escapeshellarg($scriptPath);
        foreach ($args as $arg) {
            $cmd .= ' '.escapeshellarg($arg);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($cmd, $descriptors, $pipes, $this->projectRoot());
        self::assertIsResource($proc);

        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);

        $exitCode = proc_close($proc);

        return [$exitCode, $stdout, $stderr];
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}
