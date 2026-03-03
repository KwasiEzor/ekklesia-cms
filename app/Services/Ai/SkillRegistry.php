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
    private Collection $skills;

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
        return $this->skills->groupBy(fn (AiSkill $skill) => $skill->category());
    }

    public function detectSkill(string $message): ?AiSkill
    {
        // Check for explicit /slug invocation
        if (preg_match('/^\/([a-z-]+)/', trim($message), $matches)) {
            return $this->find($matches[1]);
        }

        return null;
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
