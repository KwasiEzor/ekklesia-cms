<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class ContentWriteSkill extends AiSkill
{
    public function slug(): string
    {
        return 'content-write';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Rédacteur de contenu' : 'Content Writer';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Rédiger des annonces, pages ou articles à partir de quelques instructions'
            : 'Draft announcements, page content, or blog posts from brief instructions';
    }

    public function category(): string
    {
        return 'content';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande la rédaction de contenu. Adapte le ton au type de contenu :
- Annonces : concis, informatif, avec les détails essentiels (date, lieu, heure)
- Pages : professionnel, accueillant, reflétant les valeurs de l'église
- Articles : engageant, inspirant, avec des références bibliques si approprié
Inclus un titre accrocheur et structure le texte avec des paragraphes clairs.
PROMPT;
    }
}
