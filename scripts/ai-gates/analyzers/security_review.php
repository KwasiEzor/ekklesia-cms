<?php

declare(strict_types=1);

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require __DIR__.'/../../../vendor/autoload.php';

final class SecurityReviewVisitor extends NodeVisitorAbstract
{
    /** @var array<int, array<string, mixed>> */
    private array $findings = [];

    public function __construct(private readonly string $file) {}

    public function enterNode(Node $node): null
    {
        if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier) {
            $method = strtolower($node->name->toString());

            if (in_array($method, ['create', 'update', 'fill', 'forcefill'], true)) {
                foreach ($node->args as $arg) {
                    if ($this->isRequestAllCall($arg->value)) {
                        $this->addFinding(
                            line: $node->getStartLine(),
                            severity: 'high',
                            code: 'SEC_MASS_ASSIGN_ALL',
                            message: 'Avoid passing $request->all() into mass assignment calls.'
                        );
                    }
                }
            }

            if ($method === 'make' && $node->var instanceof Node\Name && strtolower($node->var->toString()) === 'validator') {
                foreach ($node->args as $arg) {
                    if ($this->isRequestAllCall($arg->value)) {
                        $this->addFinding(
                            line: $node->getStartLine(),
                            severity: 'medium',
                            code: 'SEC_VALIDATOR_ALL',
                            message: 'Prefer validated() payload or explicit input keys over $request->all().'
                        );
                    }
                }
            }

            if ($method === 'move' && $node->var instanceof Node\Expr\MethodCall) {
                if ($node->var->name instanceof Node\Identifier && strtolower($node->var->name->toString()) === 'file') {
                    $this->addFinding(
                        line: $node->getStartLine(),
                        severity: 'medium',
                        code: 'SEC_FILE_MOVE',
                        message: 'Raw file move detected; confirm FormRequest validation and storage disk controls.'
                    );
                }
            }
        }

        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name && $node->name instanceof Node\Identifier) {
            $class = strtolower($node->class->toString());
            $method = strtolower($node->name->toString());

            if (($class === 'validator' || $class === 'illuminate\\support\\facades\\validator') && $method === 'make') {
                foreach ($node->args as $arg) {
                    if ($this->isRequestAllCall($arg->value)) {
                        $this->addFinding(
                            line: $node->getStartLine(),
                            severity: 'medium',
                            code: 'SEC_VALIDATOR_ALL',
                            message: 'Prefer validated() payload or explicit input keys over $request->all().'
                        );
                    }
                }
            }
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    public function findings(): array
    {
        return $this->findings;
    }

    private function isRequestAllCall(Node\Expr $expr): bool
    {
        if (! $expr instanceof Node\Expr\MethodCall) {
            return false;
        }

        if (! $expr->name instanceof Node\Identifier || strtolower($expr->name->toString()) !== 'all') {
            return false;
        }

        return $expr->var instanceof Node\Expr\Variable;
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
            'message' => 'Unable to parse PHP for security review: '.$e->getMessage(),
        ];

        continue;
    }

    if ($ast === null) {
        continue;
    }

    $visitor = new SecurityReviewVisitor($file);
    $traverser = new NodeTraverser;
    $traverser->addVisitor($visitor);
    $traverser->traverse($ast);

    foreach ($visitor->findings() as $finding) {
        $results[] = $finding;
    }
}

echo json_encode(['findings' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
