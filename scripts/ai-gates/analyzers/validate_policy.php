<?php

declare(strict_types=1);

$options = getopt('', ['policy:']);
$policyPath = $options['policy'] ?? 'scripts/ai-gates/policy.json';

$errors = [];
$warnings = [];
$allowedGates = ['tenant_isolation', 'security_review', '*'];

if (! is_file($policyPath)) {
    $errors[] = "Policy file not found: {$policyPath}";
    outputAndExit(false, $errors, $warnings);
}

$content = file_get_contents($policyPath);
if (! is_string($content) || trim($content) === '') {
    $errors[] = 'Policy file is empty.';
    outputAndExit(false, $errors, $warnings);
}

$decoded = json_decode($content, true);
if (! is_array($decoded)) {
    $errors[] = 'Policy file is not valid JSON object.';
    outputAndExit(false, $errors, $warnings);
}

$allowlist = $decoded['allowlist'] ?? null;
if (! is_array($allowlist)) {
    $errors[] = 'Policy must include an array field: allowlist.';
    outputAndExit(false, $errors, $warnings);
}

$thresholds = $decoded['thresholds'] ?? [];
if (! is_array($thresholds)) {
    $errors[] = 'Policy thresholds must be an object when provided.';
    outputAndExit(false, $errors, $warnings);
}

foreach ($allowlist as $index => $rule) {
    $prefix = "allowlist[{$index}]";

    if (! is_array($rule)) {
        $errors[] = "{$prefix} must be an object.";

        continue;
    }

    $gate = (string) ($rule['gate'] ?? '*');
    $code = (string) ($rule['code'] ?? '*');
    $pathPattern = (string) ($rule['path_pattern'] ?? '');
    $lineExists = array_key_exists('line', $rule);
    $reason = (string) ($rule['reason'] ?? '');

    if (! in_array($gate, $allowedGates, true)) {
        $errors[] = "{$prefix}.gate must be one of: ".implode(', ', $allowedGates);
    }

    if ($code !== '*' && preg_match('/^[A-Z0-9_]+$/', $code) !== 1) {
        $errors[] = "{$prefix}.code must be '*' or uppercase snake case.";
    }

    if ($pathPattern !== '') {
        set_error_handler(static function (): bool {
            return true;
        });
        $ok = @preg_match('/'.$pathPattern.'/', 'app/Example.php');
        restore_error_handler();
        if ($ok === false) {
            $errors[] = "{$prefix}.path_pattern is not a valid regex fragment.";
        }
    }

    if ($lineExists && (! is_int($rule['line']) || $rule['line'] < 1)) {
        $errors[] = "{$prefix}.line must be a positive integer when provided.";
    }

    if (mb_strlen(trim($reason)) < 12) {
        $errors[] = "{$prefix}.reason must be at least 12 characters.";
    }

    if ($gate === '*' && $code === '*' && $pathPattern === '' && ! $lineExists) {
        $errors[] = "{$prefix} is too broad: wildcard gate+code requires path_pattern or line.";
    }

    if ($gate !== '*' && $code === '*' && $pathPattern === '' && ! $lineExists) {
        $warnings[] = "{$prefix} suppresses all findings for gate {$gate}; prefer narrower scope.";
    }
}

validateThresholds($thresholds, $errors);

outputAndExit($errors === [], $errors, $warnings);

function validateThresholds(array $thresholds, array &$errors): void
{
    $tenantFail = $thresholds['tenant_isolation']['fail_on'] ?? null;
    if ($tenantFail !== null && ! isSeverityList($tenantFail)) {
        $errors[] = 'thresholds.tenant_isolation.fail_on must be an array of severities.';
    }

    $securityFail = $thresholds['security_review']['fail_on'] ?? null;
    if ($securityFail !== null && ! isSeverityList($securityFail)) {
        $errors[] = 'thresholds.security_review.fail_on must be an array of severities.';
    }

    if (array_key_exists('test_gaps', $thresholds)) {
        $minCoverage = $thresholds['test_gaps']['min_coverage'] ?? null;
        if (! is_int($minCoverage) && ! is_float($minCoverage)) {
            $errors[] = 'thresholds.test_gaps.min_coverage must be numeric.';
        } elseif ($minCoverage < 0 || $minCoverage > 100) {
            $errors[] = 'thresholds.test_gaps.min_coverage must be between 0 and 100.';
        }
    }
}

function isSeverityList(mixed $value): bool
{
    if (! is_array($value)) {
        return false;
    }

    $allowed = ['critical', 'high', 'medium', 'low'];
    foreach ($value as $item) {
        if (! is_string($item)) {
            return false;
        }

        if (! in_array(strtolower($item), $allowed, true)) {
            return false;
        }
    }

    return true;
}

function outputAndExit(bool $valid, array $errors, array $warnings): void
{
    echo json_encode([
        'valid' => $valid,
        'errors' => $errors,
        'warnings' => $warnings,
        'counts' => [
            'errors' => count($errors),
            'warnings' => count($warnings),
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

    exit($valid ? 0 : 1);
}
