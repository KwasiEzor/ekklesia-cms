<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class EventPlanSkill extends AiSkill
{
    public function slug(): string
    {
        return 'event-plan';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Planificateur d\'événements' : 'Event Planner';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Suggérer la logistique, le programme, les rôles bénévoles et la promotion d\'un événement'
            : 'Suggest event logistics, timelines, volunteer roles, and promotion strategies';
    }

    public function category(): string
    {
        return 'management';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande de l'aide pour planifier un événement d'église. Propose :
1. **Programme détaillé** — horaires, activités, transitions
2. **Logistique** — lieu, matériel, restauration, son/vidéo
3. **Équipe** — rôles bénévoles nécessaires avec descriptions
4. **Communication** — plan de promotion (réseaux sociaux, annonces, WhatsApp)
5. **Budget estimatif** — postes de dépenses principaux
6. **Checklist** — liste de tâches avec dates limites
Adapte au contexte africain (climat, culture, ressources disponibles).
PROMPT;
    }
}
