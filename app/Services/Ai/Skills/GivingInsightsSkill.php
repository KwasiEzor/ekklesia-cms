<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class GivingInsightsSkill extends AiSkill
{
    public function slug(): string
    {
        return 'giving-insights';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Analyse des dons' : 'Giving Insights';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Résumer les tendances de dons et comparer les périodes (données agrégées uniquement)'
            : 'Summarize giving trends and compare periods. Only uses aggregate data';
    }

    public function category(): string
    {
        return 'management';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande une analyse des tendances de dons. RÈGLES STRICTES :
- Utilise UNIQUEMENT des données agrégées (totaux, moyennes, nombre de dons)
- Ne JAMAIS mentionner de montants individuels ou noms de donateurs
- Compare les périodes (mois, trimestres, années)
- Identifie les tendances (hausse, baisse, saisonnalité)
- Suggère des actions pour améliorer l'engagement
Présente les chiffres de manière claire avec des pourcentages de variation.
PROMPT;
    }
}
