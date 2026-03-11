<?php

namespace App\Services\Ai;

use App\Services\Ai\Skills\BrandAdviseSkill;
use App\Services\Ai\Skills\CommDraftSkill;
use App\Services\Ai\Skills\ContentAuditSkill;
use App\Services\Ai\Skills\ContentWriteSkill;
use App\Services\Ai\Skills\DashboardNarrateSkill;
use App\Services\Ai\Skills\DataQualitySkill;
use App\Services\Ai\Skills\EventPlanSkill;
use App\Services\Ai\Skills\GivingInsightsSkill;
use App\Services\Ai\Skills\ProofreadSkill;
use App\Services\Ai\Skills\SeoOptimizeSkill;
use App\Services\Ai\Skills\SermonOutlineSkill;
use App\Services\Ai\Skills\SocialCreateSkill;
use App\Services\Ai\Skills\StrategyAdviseSkill;
use App\Services\Ai\Skills\TranslateSkill;
use Illuminate\Support\Collection;

class SkillRegistry
{
    /** @var Collection<string, AiSkill> */
    private readonly Collection $skills;

    public function __construct()
    {
        $this->skills = collect();
        $this->registerDefaults();
    }

    public function register(AiSkill $skill): void
    {
        $this->skills->put($skill->slug(), $skill);
    }

    public function find(string $slug): ?AiSkill
    {
        return $this->skills->get($slug);
    }

    /** @return Collection<string, AiSkill> */
    public function all(): Collection
    {
        return $this->skills;
    }

    /** @return Collection<string, Collection<string, AiSkill>> */
    public function byCategory(): Collection
    {
        return $this->skills
            ->groupBy(fn (AiSkill $skill): string => $skill->category())
            ->map(
                fn (Collection $skills): Collection => $skills
                    ->filter(fn ($skill): bool => $skill instanceof AiSkill)
                    ->mapWithKeys(fn (AiSkill $skill): array => [$skill->slug() => $skill])
            );
    }

    public function detectSkill(string $message): ?AiSkill
    {
        $message = trim($message);

        // 1. Check for explicit /slug invocation
        if (preg_match('/^\/([a-z-]+)/', $message, $matches)) {
            return $this->find($matches[1]);
        }

        // 2. Keyword-based detection with scoring
        $scores = collect();
        $lowercaseMessage = mb_strtolower($message);

        foreach ($this->skills as $skill) {
            $score = 0;
            $keywords = $this->getKeywordsForSkill($skill);

            foreach ($keywords as $keyword) {
                if (str_contains($lowercaseMessage, mb_strtolower($keyword))) {
                    $score++;
                }
            }

            if ($score > 0) {
                $scores->put($skill->slug(), [
                    'skill' => $skill,
                    'score' => $score,
                ]);
            }
        }

        if ($scores->isEmpty()) {
            return null;
        }

        // Return skill with highest score
        return $scores->sortByDesc('score')->first()['skill'];
    }

    /** @return string[] */
    private function getKeywordsForSkill(AiSkill $skill): array
    {
        $defaults = match ($skill->slug()) {
            'sermon-outline' => ['sermon', 'prédication', 'plan', 'outline', 'message'],
            'giving-insights' => ['don', 'giving', 'argent', 'finance', 'stat', 'tendance', 'montant', 'offrande', 'dîme'],
            'event-plan' => ['événement', 'event', 'planifier', 'organisation', 'fête', 'culte'],
            'translate' => ['traduire', 'translate', 'traduction', 'anglais', 'français'],
            'proofread' => ['corriger', 'fautes', 'orthographe', 'relecture', 'proofread'],
            'seo-optimize' => ['seo', 'référencement', 'google', 'mots-clés'],
            'content-write' => ['écrire', 'rédiger', 'article', 'blog', 'texte'],
            'social-create' => ['facebook', 'instagram', 'réseaux', 'post', 'social'],
            default => [],
        };

        return array_unique(array_merge($defaults, $skill->getKeywords()));
    }

    private function registerDefaults(): void
    {
        $defaults = [
            new SermonOutlineSkill,
            new ContentWriteSkill,
            new TranslateSkill,
            new SeoOptimizeSkill,
            new ProofreadSkill,
            new EventPlanSkill,
            new CommDraftSkill,
            new GivingInsightsSkill,
            new DashboardNarrateSkill,
            new BrandAdviseSkill,
            new SocialCreateSkill,
            new ContentAuditSkill,
            new DataQualitySkill,
            new StrategyAdviseSkill,
        ];

        foreach ($defaults as $skill) {
            $this->register($skill);
        }
    }
}
