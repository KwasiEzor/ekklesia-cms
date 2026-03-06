<?php

declare(strict_types=1);

$options = getopt('', ['diff-range:', 'clover:', 'threshold::']);
$diffRange = $options['diff-range'] ?? 'HEAD~1...HEAD';
$cloverPath = $options['clover'] ?? 'artifacts/clover.xml';
$threshold = isset($options['threshold']) ? (float) $options['threshold'] : 85.0;

$diffOutput = shell_exec('git diff --unified=0 --no-color '.escapeshellarg($diffRange).' -- "app/**/*.php" "routes/api.php"');
if (! is_string($diffOutput)) {
    fwrite(STDERR, "Unable to compute git diff.\n");
    exit(2);
}

$changedLines = parseChangedLines($diffOutput);
if ($changedLines === []) {
    echo json_encode([
        'status' => 'pass',
        'threshold' => $threshold,
        'covered' => 0,
        'total' => 0,
        'percent' => 100.0,
        'message' => 'No changed executable PHP lines in critical scope.',
        'files' => [],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(0);
}

if (! is_file($cloverPath)) {
    fwrite(STDERR, "Clover file not found at {$cloverPath}.\n");
    exit(2);
}

$xml = simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Unable to parse clover XML.\n");
    exit(2);
}

$coverageByFile = buildCoverageMap($xml);
$projectRoot = realpath(__DIR__.'/../../..') ?: getcwd();

$total = 0;
$covered = 0;
$filesSummary = [];

foreach ($changedLines as $relativeFile => $lines) {
    $abs = realpath($projectRoot.DIRECTORY_SEPARATOR.$relativeFile);
    if ($abs === false) {
        continue;
    }

    $stmtMap = $coverageByFile[$abs] ?? [];
    $fileTotal = 0;
    $fileCovered = 0;

    foreach ($lines as $line) {
        if (! array_key_exists($line, $stmtMap)) {
            continue;
        }

        $fileTotal++;
        if ($stmtMap[$line] > 0) {
            $fileCovered++;
        }
    }

    if ($fileTotal > 0) {
        $total += $fileTotal;
        $covered += $fileCovered;
        $filesSummary[] = [
            'file' => $relativeFile,
            'covered' => $fileCovered,
            'total' => $fileTotal,
            'percent' => round(($fileCovered / $fileTotal) * 100, 2),
        ];
    }
}

$percent = $total > 0 ? round(($covered / $total) * 100, 2) : 100.0;
$status = $percent >= $threshold ? 'pass' : 'fail';

if ($total === 0) {
    $status = 'pass';
}

echo json_encode([
    'status' => $status,
    'threshold' => $threshold,
    'covered' => $covered,
    'total' => $total,
    'percent' => $percent,
    'message' => $total === 0
        ? 'Changed lines are non-executable or missing from clover statement map.'
        : 'Computed changed-lines statement coverage for critical scope.',
    'files' => $filesSummary,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($status === 'pass' ? 0 : 1);

/**
 * @return array<string, array<int, int>>
 */
function buildCoverageMap(SimpleXMLElement $xml): array
{
    $map = [];
    foreach ($xml->project->xpath('.//file') ?: [] as $fileNode) {
        $fileName = (string) $fileNode['name'];
        if ($fileName === '') {
            continue;
        }

        $abs = realpath($fileName) ?: $fileName;
        $statements = [];

        foreach ($fileNode->line as $lineNode) {
            $type = (string) $lineNode['type'];
            if ($type !== 'stmt') {
                continue;
            }

            $line = (int) $lineNode['num'];
            $count = (int) $lineNode['count'];
            $statements[$line] = $count;
        }

        $map[$abs] = $statements;
    }

    return $map;
}

/**
 * @return array<string, array<int, int>>
 */
function parseChangedLines(string $diff): array
{
    $result = [];
    $currentFile = null;

    foreach (explode("\n", $diff) as $line) {
        if (str_starts_with($line, '+++ b/')) {
            $currentFile = substr($line, 6);
            if ($currentFile !== null && ! array_key_exists($currentFile, $result)) {
                $result[$currentFile] = [];
            }

            continue;
        }

        if (! str_starts_with($line, '@@') || $currentFile === null) {
            continue;
        }

        if (! preg_match('/\+([0-9]+)(?:,([0-9]+))?/', $line, $matches)) {
            continue;
        }

        $start = (int) $matches[1];
        $count = isset($matches[2]) ? (int) $matches[2] : 1;

        if ($count <= 0) {
            continue;
        }

        for ($i = 0; $i < $count; $i++) {
            $result[$currentFile][$start + $i] = $start + $i;
        }
    }

    foreach ($result as $file => $lines) {
        if ($lines === []) {
            unset($result[$file]);

            continue;
        }

        ksort($lines);
        $result[$file] = $lines;
    }

    return $result;
}
