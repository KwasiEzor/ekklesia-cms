<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/config',
        __DIR__.'/routes',
    ])
    ->withSkip([
        __DIR__.'/vendor',
        __DIR__.'/node_modules',
        __DIR__.'/docs',
    ])
    ->withPhpSets(php84: true)
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ]);
