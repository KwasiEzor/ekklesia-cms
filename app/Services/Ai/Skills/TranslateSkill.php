<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class TranslateSkill extends AiSkill
{
    public function slug(): string
    {
        return 'translate';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Traducteur' : 'Translator';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Traduire du contenu entre français et anglais avec connaissance du vocabulaire ecclésial'
            : 'Translate content between French and English with church terminology awareness';
    }

    public function category(): string
    {
        return 'content';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande une traduction. Règles :
- Détecte automatiquement la langue source (FR→EN ou EN→FR)
- Préserve le vocabulaire ecclésial spécifique (ex: "culte" → "worship service", "cellule" → "cell group")
- Maintiens le ton et le registre du texte original
- Pour les versets bibliques, utilise les traductions officielles (LSG pour FR, NIV/ESV pour EN)
- Fournis la traduction directement sans explication superflue
PROMPT;
    }
}
