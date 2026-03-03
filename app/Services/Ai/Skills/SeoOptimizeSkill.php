<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class SeoOptimizeSkill extends AiSkill
{
    public function slug(): string
    {
        return 'seo-optimize';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Optimiseur SEO' : 'SEO Optimizer';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Générer des titres SEO, méta-descriptions, mots-clés et suggestions Open Graph'
            : 'Generate SEO titles, meta descriptions, keywords, and Open Graph suggestions';
    }

    public function category(): string
    {
        return 'content';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande une optimisation SEO. Fournis :
1. **Titre SEO** (50-60 caractères) — accrocheur avec mot-clé principal
2. **Méta-description** (150-160 caractères) — résumé engageant avec appel à l'action
3. **Mots-clés** (5-10) — pertinents pour le contexte ecclésial africain
4. **Titre Open Graph** — optimisé pour le partage social
5. **Description Open Graph** — engageante pour Facebook/WhatsApp
Pense au référencement local et aux termes que les fidèles utiliseraient pour chercher.
PROMPT;
    }
}
