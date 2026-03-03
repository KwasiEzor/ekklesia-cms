<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class DashboardNarrateSkill extends AiSkill
{
    public function slug(): string
    {
        return 'dashboard-narrate';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Narrateur de tableau de bord' : 'Dashboard Narrator';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Générer un résumé en langage naturel de l\'état actuel de l\'église'
            : 'Generate a natural-language summary of the church\'s current state from dashboard stats';
    }

    public function category(): string
    {
        return 'management';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande un résumé narratif du tableau de bord. Génère un texte fluide qui :
- Résume l'état actuel de l'église en 2-3 paragraphes
- Met en valeur les points positifs et les réussites
- Signale les points d'attention (contenu ancien, baisse d'activité)
- Utilise un ton encourageant et constructif
- Inclut des suggestions concrètes d'amélioration
Utilise les statistiques fournies dans le contexte du système.
PROMPT;
    }
}
