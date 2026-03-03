<?php

namespace App\Services\Ai;

abstract class AiSkill
{
    abstract public function slug(): string;

    abstract public function name(string $locale = 'fr'): string;

    abstract public function description(string $locale = 'fr'): string;

    abstract public function category(): string;

    abstract public function systemPromptAddition(): string;
}
