<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class ContentAuditSkill extends AiSkill
{
    public function slug(): string
    {
        return 'content-audit';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Auditeur de contenu' : 'Content Auditor';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Identifier le contenu obsolète, pages incomplètes et annonces expirées'
            : 'Scan for outdated content, incomplete pages, missing images, and stale announcements';
    }

    public function category(): string
    {
        return 'maintenance';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande un audit de contenu. Analyse les données disponibles et signale :
1. **Contenu obsolète** — prédications/événements anciens sans mise à jour
2. **Pages incomplètes** — pages sans contenu ou avec peu de blocs
3. **Annonces expirées** — annonces qui devraient être archivées
4. **Contenu manquant** — types de contenu attendus mais absents
5. **Recommandations** — actions prioritaires pour améliorer le site
Classe les problèmes par priorité (critique, important, mineur).
PROMPT;
    }
}
