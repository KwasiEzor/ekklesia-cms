<?php

declare(strict_types=1);

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require __DIR__.'/../../../vendor/autoload.php';

final class TenantIsolationVisitor extends NodeVisitorAbstract
{
    /** @var array<int, array<string, mixed>> */
    private array $findings = [];

    public function __construct(private readonly string $file) {}

    public function enterNode(Node $node): null
    {
        if ($node instanceof Node\Expr\StaticCall) {
            $class = $node->class instanceof Node\Name ? $node->class->toString() : null;
            $method = $node->name instanceof Node\Identifier ? $node->name->toString() : null;

            if ($class !== null && $method !== null) {
                $classLc = strtolower($class);
                $methodLc = strtolower($method);

                if (($classLc === 'db' || $classLc === 'illuminate\\support\\facades\\db') && $methodLc === 'table') {
                    $this->addFinding(
                        line: $node->getStartLine(),
                        severity: 'high',
                        code: 'TENANCY_DB_TABLE',
                        message: 'DB::table() bypasses model/global-scope tenant guarantees.'
                    );
                }
            }
        }

        if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier) {
            $method = strtolower($node->name->toString());
            if ($method === 'withoutglobalscope' || $method === 'withoutglobalscopes') {
                $this->addFinding(
                    line: $node->getStartLine(),
                    severity: 'high',
                    code: 'TENANCY_SCOPE_BYPASS',
                    message: 'withoutGlobalScope(s) can remove tenant protections.'
                );
            }
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    public function findings(): array
    {
        return $this->findings;
    }

    private function addFinding(int $line, string $severity, string $code, string $message): void
    {
        $this->findings[] = [
            'file' => $this->file,
            'line' => $line,
            'severity' => $severity,
            'code' => $code,
            'message' => $message,
        ];
    }
}

$files = array_values(array_filter(array_map('trim', explode("\n", stream_get_contents(STDIN) ?: ''))));
$results = [];
$parser = (new ParserFactory)->createForHostVersion();

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    $code = file_get_contents($file);
    if ($code === false) {
        continue;
    }

    try {
        $ast = $parser->parse($code);
    } catch (Error $e) {
        $results[] = [
            'file' => $file,
            'line' => 1,
            'severity' => 'medium',
            'code' => 'PARSE_ERROR',
            'message' => 'Unable to parse PHP for tenant audit: '.$e->getMessage(),
        ];

        continue;
    }

    if ($ast === null) {
        continue;
    }

    $visitor = new TenantIsolationVisitor($file);
    $traverser = new NodeTraverser;
    $traverser->addVisitor($visitor);
    $traverser->traverse($ast);

    foreach ($visitor->findings() as $finding) {
        $results[] = $finding;
    }
}

echo json_encode(['findings' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
