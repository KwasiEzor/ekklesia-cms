<?php

use App\Services\Ai\SkillRegistry;

test('skill registry registers 14 default skills', function () {
    $registry = new SkillRegistry;

    expect($registry->all())->toHaveCount(14);
});

test('skill registry finds skill by slug', function () {
    $registry = new SkillRegistry;

    $skill = $registry->find('sermon-outline');
    expect($skill)->not->toBeNull();
    expect($skill->slug())->toBe('sermon-outline');
});

test('skill registry returns null for unknown slug', function () {
    $registry = new SkillRegistry;

    expect($registry->find('nonexistent'))->toBeNull();
});

test('skill registry detects skill from /command syntax', function () {
    $registry = new SkillRegistry;

    $skill = $registry->detectSkill('/translate Bonjour tout le monde');
    expect($skill)->not->toBeNull();
    expect($skill->slug())->toBe('translate');
});

test('skill registry returns null when no /command prefix', function () {
    $registry = new SkillRegistry;

    expect($registry->detectSkill('Help me write a sermon'))->toBeNull();
});

test('skills have french and english names', function () {
    $registry = new SkillRegistry;

    $skill = $registry->find('sermon-outline');
    expect($skill->name('fr'))->toBe('Plan de prédication');
    expect($skill->name('en'))->toBe('Sermon Outliner');
});

test('skills are grouped by category', function () {
    $registry = new SkillRegistry;
    $categories = $registry->byCategory();

    expect($categories)->toHaveKey('content');
    expect($categories)->toHaveKey('management');
    expect($categories)->toHaveKey('design');
    expect($categories)->toHaveKey('maintenance');
    expect($categories)->toHaveKey('guidance');
});

test('all skills have system prompt additions', function () {
    $registry = new SkillRegistry;

    foreach ($registry->all() as $skill) {
        expect($skill->systemPromptAddition())->toBeString()->not->toBeEmpty();
    }
});

test('all skill slugs are unique', function () {
    $registry = new SkillRegistry;

    $slugs = $registry->all()->map(fn ($s) => $s->slug())->toArray();
    expect($slugs)->toHaveCount(count(array_unique($slugs)));
});
