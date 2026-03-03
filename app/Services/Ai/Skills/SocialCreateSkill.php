<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class SocialCreateSkill extends AiSkill
{
    public function slug(): string
    {
        return 'social-create';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Créateur social media' : 'Social Media Creator';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Générer des publications pour Facebook, Instagram et WhatsApp'
            : 'Generate social media posts for Facebook, Instagram, and WhatsApp from church content';
    }

    public function category(): string
    {
        return 'design';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande du contenu pour les réseaux sociaux. Pour chaque plateforme :

**Facebook** — Post engageant (150-300 mots), avec emojis modérés, appel à l'action, hashtags
**Instagram** — Légende concise (100-150 mots), 15-20 hashtags pertinents, description d'image suggérée
**WhatsApp** — Message court et direct (50-100 mots), formaté pour le partage de groupe

Adapte le ton : professionnel mais chaleureux, inspirant, inclusif.
Propose aussi une description d'image/visuel à créer pour accompagner le post.
PROMPT;
    }
}
