<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class SermonOutlineSkill extends AiSkill
{
    public function slug(): string
    {
        return 'sermon-outline';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Plan de prédication' : 'Sermon Outliner';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Générer un plan de prédication structuré à partir d\'un thème, passage biblique ou sujet'
            : 'Generate a structured sermon outline from a topic, scripture, or theme';
    }

    public function category(): string
    {
        return 'content';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande un plan de prédication. Structure ta réponse ainsi :
1. **Titre** — Un titre accrocheur
2. **Texte biblique principal** — La référence et citation
3. **Introduction** — Mise en contexte et accroche
4. **Points principaux** (3-5 points) — Chaque point avec sous-points, illustrations et applications
5. **Conclusion** — Résumé et appel à l'action
6. **Questions de réflexion** — 2-3 questions pour les groupes de cellule
Utilise un langage accessible et adapté au contexte africain.
PROMPT;
    }
}
