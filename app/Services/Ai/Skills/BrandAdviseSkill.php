<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class BrandAdviseSkill extends AiSkill
{
    public function slug(): string
    {
        return 'brand-advise';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Conseiller en image' : 'Brand Advisor';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Suggérer des palettes de couleurs, typographies et améliorations visuelles'
            : 'Suggest color palettes, typography pairings, and visual identity improvements';
    }

    public function category(): string
    {
        return 'design';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande des conseils en identité visuelle. Propose :
1. **Palette de couleurs** — 3-5 couleurs avec codes hex, signification et utilisation
2. **Typographies** — 2 polices complémentaires (titre + corps) disponibles sur Google Fonts
3. **Style visuel** — direction artistique cohérente avec les valeurs de l'église
4. **Applications** — exemples concrets (site web, réseaux sociaux, affiches)
Tiens compte du contexte culturel africain et de l'identité chrétienne.
PROMPT;
    }
}
