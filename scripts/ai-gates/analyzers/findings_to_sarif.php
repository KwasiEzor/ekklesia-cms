<?php

declare(strict_types=1);

$options = getopt('', ['gate:']);
$gate = $options['gate'] ?? 'ai-gate';

$raw = stream_get_contents(STDIN) ?: '';
$data = json_decode($raw, true);
$findings = [];
if (is_array($data['active_findings'] ?? null)) {
    $findings = $data['active_findings'];
} elseif (is_array($data['findings'] ?? null)) {
    $findings = $data['findings'];
}

$rules = [];
$ruleIds = [];
$results = [];

foreach ($findings as $finding) {
    if (! is_array($finding)) {
        continue;
    }

    $ruleId = (string) ($finding['code'] ?? 'AI_GATE_FINDING');
    if ($ruleId === '') {
        $ruleId = 'AI_GATE_FINDING';
    }

    if (! isset($ruleIds[$ruleId])) {
        $ruleIds[$ruleId] = true;
        $rules[] = [
            'id' => $ruleId,
            'name' => $ruleId,
            'shortDescription' => ['text' => $ruleId],
            'help' => [
                'text' => (string) ($finding['message'] ?? 'AI gate finding.'),
            ],
        ];
    }

    $file = (string) ($finding['file'] ?? 'unknown');
    $line = max(1, (int) ($finding['line'] ?? 1));
    $severity = strtolower((string) ($finding['severity'] ?? 'medium'));
    $level = match ($severity) {
        'high', 'critical' => 'error',
        'low' => 'note',
        default => 'warning',
    };

    $results[] = [
        'ruleId' => $ruleId,
        'level' => $level,
        'message' => [
            'text' => (string) ($finding['message'] ?? 'AI gate finding.'),
        ],
        'locations' => [[
            'physicalLocation' => [
                'artifactLocation' => [
                    'uri' => $file,
                    'uriBaseId' => '%SRCROOT%',
                ],
                'region' => [
                    'startLine' => $line,
                ],
            ],
        ]],
    ];
}

$sarif = [
    '$schema' => 'https://json.schemastore.org/sarif-2.1.0.json',
    'version' => '2.1.0',
    'runs' => [[
        'tool' => [
            'driver' => [
                'name' => 'ekklesia-ai-gates',
                'informationUri' => 'https://github.com/KwasiEzor/ekklesia-cms',
                'rules' => $rules,
            ],
        ],
        'automationDetails' => [
            'id' => $gate,
        ],
        'originalUriBaseIds' => [
            '%SRCROOT%' => [
                'uri' => 'file:///',
            ],
        ],
        'results' => $results,
    ]],
];

echo json_encode($sarif, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
