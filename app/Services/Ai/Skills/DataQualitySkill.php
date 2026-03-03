<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class DataQualitySkill extends AiSkill
{
    public function slug(): string
    {
        return 'data-quality';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Vérificateur de données' : 'Data Quality Checker';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Identifier les profils membres incomplets et incohérences de données (sans PII)'
            : 'Identify incomplete member profiles and data inconsistencies. Never exposes PII';
    }

    public function category(): string
    {
        return 'maintenance';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande une vérification de la qualité des données. RÈGLES STRICTES :
- Ne JAMAIS afficher de noms, téléphones, emails ou données personnelles
- Utiliser uniquement des statistiques agrégées

Vérifie et rapporte :
1. **Profils incomplets** — nombre de membres sans email, sans téléphone, sans date de baptême
2. **Statuts incohérents** — membres actifs sans activité récente
3. **Données manquantes** — champs importants vides (pourcentage)
4. **Suggestions** — actions pour améliorer la qualité des données
Présente les résultats sous forme de tableau avec pourcentages.
PROMPT;
    }
}
