<?php

declare(strict_types=1);

$options = getopt('', ['gate:', 'policy:', 'baseline:']);
$gate = $options['gate'] ?? '';
$policyPath = $options['policy'] ?? 'scripts/ai-gates/policy.json';
$baselinePath = $options['baseline'] ?? 'scripts/ai-gates/baseline.json';

$raw = stream_get_contents(STDIN) ?: '';
$parsed = json_decode($raw, true);
$findings = is_array($parsed['findings'] ?? null) ? $parsed['findings'] : [];

$policy = ['allowlist' => []];
if (is_file($policyPath)) {
    $policyJson = file_get_contents($policyPath);
    if (is_string($policyJson) && $policyJson !== '') {
        $decoded = json_decode($policyJson, true);
        if (is_array($decoded)) {
            $policy = $decoded;
        }
    }
}

$allowlist = is_array($policy['allowlist'] ?? null) ? $policy['allowlist'] : [];
$baseline = ['suppress_fingerprints' => []];
if (is_file($baselinePath)) {
    $baselineJson = file_get_contents($baselinePath);
    if (is_string($baselineJson) && $baselineJson !== '') {
        $decoded = json_decode($baselineJson, true);
        if (is_array($decoded)) {
            $baseline = $decoded;
        }
    }
}
$baselineSuppress = is_array($baseline['suppress_fingerprints'] ?? null) ? $baseline['suppress_fingerprints'] : [];
$baselineSet = [];
foreach ($baselineSuppress as $item) {
    if (is_string($item) && $item !== '') {
        $baselineSet[$item] = true;
    }
}

$active = [];
$suppressedPolicy = [];
$suppressedBaseline = [];

foreach ($findings as $finding) {
    if (! is_array($finding)) {
        continue;
    }

    $finding['fingerprint'] = findingFingerprint($finding);
    $isSuppressed = false;
    $suppressionType = '';
    foreach ($allowlist as $rule) {
        if (! is_array($rule)) {
            continue;
        }

        if (! matchesGate($rule, $gate)) {
            continue;
        }

        if (! matchesCode($rule, (string) ($finding['code'] ?? ''))) {
            continue;
        }

        if (! matchesPath($rule, (string) ($finding['file'] ?? ''))) {
            continue;
        }

        if (! matchesLine($rule, (int) ($finding['line'] ?? 0))) {
            continue;
        }

        $isSuppressed = true;
        $suppressionType = 'policy';
        $finding['suppressed_reason'] = (string) ($rule['reason'] ?? 'suppressed by policy');
        break;
    }

    if (! $isSuppressed && isset($baselineSet[(string) ($finding['fingerprint'] ?? '')])) {
        $isSuppressed = true;
        $suppressionType = 'baseline';
        $finding['suppressed_reason'] = 'suppressed by baseline snapshot';
    }

    if ($isSuppressed) {
        if ($suppressionType === 'baseline') {
            $suppressedBaseline[] = $finding;
        } else {
            $suppressedPolicy[] = $finding;
        }

        continue;
    }

    $active[] = $finding;
}

echo json_encode([
    'gate' => $gate,
    'active_findings' => $active,
    'suppressed_findings' => array_values(array_merge($suppressedPolicy, $suppressedBaseline)),
    'suppressed_policy_findings' => $suppressedPolicy,
    'suppressed_baseline_findings' => $suppressedBaseline,
    'counts' => [
        'active' => count($active),
        'suppressed_policy' => count($suppressedPolicy),
        'suppressed_baseline' => count($suppressedBaseline),
        'suppressed' => count($suppressedPolicy) + count($suppressedBaseline),
        'total' => count($active) + count($suppressedPolicy) + count($suppressedBaseline),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

function matchesGate(array $rule, string $gate): bool
{
    $ruleGate = (string) ($rule['gate'] ?? '*');

    return $ruleGate === '*' || $ruleGate === $gate;
}

function matchesCode(array $rule, string $code): bool
{
    $ruleCode = (string) ($rule['code'] ?? '*');

    return $ruleCode === '*' || $ruleCode === $code;
}

function matchesPath(array $rule, string $path): bool
{
    $pattern = (string) ($rule['path_pattern'] ?? '');
    if ($pattern === '') {
        return true;
    }

    set_error_handler(static function (): bool {
        return true;
    });
    $match = preg_match('/'.$pattern.'/', $path) === 1;
    restore_error_handler();

    return $match;
}

function matchesLine(array $rule, int $line): bool
{
    if (! array_key_exists('line', $rule)) {
        return true;
    }

    return (int) $rule['line'] === $line;
}

function findingFingerprint(array $finding): string
{
    $code = (string) ($finding['code'] ?? '');
    $file = (string) ($finding['file'] ?? '');
    $line = (int) ($finding['line'] ?? 0);
    $message = (string) ($finding['message'] ?? '');

    return sha1($code.'|'.$file.'|'.$line.'|'.$message);
}
