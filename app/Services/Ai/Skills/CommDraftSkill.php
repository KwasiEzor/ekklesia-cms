<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class CommDraftSkill extends AiSkill
{
    public function slug(): string
    {
        return 'comm-draft';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Rédacteur de communications' : 'Communication Drafter';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Rédiger des messages de bienvenue, suivis, anniversaires et demandes de prière'
            : 'Draft welcome messages, follow-ups, birthday greetings, and prayer requests';
    }

    public function category(): string
    {
        return 'management';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande la rédaction d'une communication d'église. Types possibles :
- **Message de bienvenue** — chaleureux, accueillant, avec infos pratiques
- **Suivi de visite** — personnel, invitant à revenir
- **Anniversaire/fête** — joyeux avec bénédiction biblique
- **Demande de prière** — sensible, réconfortant, avec verset encourageant
- **Rappel d'événement** — informatif avec tous les détails
Utilise un ton pastoral et bienveillant. Évite le jargon excessif.
PROMPT;
    }
}
