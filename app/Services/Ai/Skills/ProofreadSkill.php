<?php

namespace App\Services\Ai\Skills;

use App\Services\Ai\AiSkill;

class ProofreadSkill extends AiSkill
{
    public function slug(): string
    {
        return 'proofread';
    }

    public function name(string $locale = 'fr'): string
    {
        return $locale === 'fr' ? 'Correcteur' : 'Proofreader';
    }

    public function description(string $locale = 'fr'): string
    {
        return $locale === 'fr'
            ? 'Vérifier la grammaire, l\'orthographe, le ton et la clarté du texte'
            : 'Check grammar, spelling, tone, and clarity. Suggest improvements while preserving voice';
    }

    public function category(): string
    {
        return 'content';
    }

    public function systemPromptAddition(): string
    {
        return <<<'PROMPT'
L'utilisateur demande une relecture/correction. Procède ainsi :
1. Corrige les fautes d'orthographe et de grammaire
2. Améliore la clarté et la fluidité
3. Vérifie la cohérence du ton (formel pour annonces, chaleureux pour communications)
4. Préserve la voix et le style de l'auteur
5. Présente le texte corrigé suivi d'un résumé des modifications apportées
PROMPT;
    }
}
