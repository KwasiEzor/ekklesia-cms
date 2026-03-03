<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class StrategyAdviseSkill extends AiSkill
{
    public function slug(): string
    {
        return 'strategy-advise';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Conseiller stratégique' : 'Strategy Advisor';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Recommander des stratégies de contenu, engagement et croissance pour l\'église'
            : 'Recommend content strategy, engagement improvements, and growth tactics';
    }

    public function category(): string
    {
        return 'guidance';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande des conseils stratégiques pour son église. Propose :
1. **Stratégie de contenu** — fréquence de publication, types de contenu à privilégier
2. **Engagement** — comment augmenter l'interaction avec les membres
3. **Croissance** — tactiques d'accueil, suivi des visiteurs, présence en ligne
4. **Communication** — canaux à utiliser, messages clés, calendrier éditorial
5. **Mesure** — indicateurs à suivre pour évaluer les progrès
Base tes recommandations sur les données de l'église et les meilleures pratiques.
PROMPT;
    }
}
