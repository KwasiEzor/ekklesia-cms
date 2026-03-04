<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Page;
use App\Models\Sermon;
use App\Services\Ai\SkillRegistry;
use Illuminate\Support\Carbon;

class TenantContextBuilder
{
    public function __construct(
        private readonly SkillRegistry $skillRegistry,
    ) {}

    public function buildSystemPrompt(string $locale = 'fr'): string
    {
        $tenant = tenant();
        if (! $tenant) {
            return $this->basePrompt($locale);
        }

        $parts = [
            $this->basePrompt($locale),
            $this->tenantContext($tenant),
            $this->aggregateStats(),
            $this->recentContent(),
            $this->skillsPrompt($locale),
            $this->securityRules($locale),
        ];

        return implode("\n\n", array_filter($parts));
    }

    private function basePrompt(string $locale): string
    {
        if ($locale === 'fr') {
            return <<<'PROMPT'
Tu es l'assistant IA d'Ekklesia CMS, un système de gestion de contenu pour les églises africaines.
Tu aides les administrateurs d'église à gérer leur contenu, leurs membres et leurs activités.
Réponds toujours de manière professionnelle, bienveillante et adaptée au contexte ecclésial.
Tu parles français par défaut, mais tu peux aussi répondre en anglais si on te le demande.
PROMPT;
        }

        return <<<'PROMPT'
You are the AI assistant for Ekklesia CMS, a content management system for African churches.
You help church administrators manage their content, members, and activities.
Always respond professionally, kindly, and in a way that's appropriate for a church context.
You speak English by default, but can also respond in French if asked.
PROMPT;
    }

    private function tenantContext(mixed $tenant): string
    {
        $name = $tenant->name ?? 'Église';
        $pastor = $tenant->pastor_name ?? null;
        $denomination = $tenant->denomination ?? null;
        $timezone = $tenant->timezone ?? 'Africa/Lome';
        $currency = $tenant->default_currency ?? 'XOF';

        $lines = ["## Contexte de l'église", "- Nom : {$name}"];

        if ($pastor) {
            $lines[] = "- Pasteur : {$pastor}";
        }
        if ($denomination) {
            $lines[] = "- Dénomination : {$denomination}";
        }

        $lines[] = "- Fuseau horaire : {$timezone}";
        $lines[] = "- Devise : {$currency}";

        return implode("\n", $lines);
    }

    private function aggregateStats(): string
    {
        $stats = [
            'Prédications' => Sermon::count(),
            'Membres (total)' => Member::count(),
            'Membres (actifs)' => Member::where('status', 'active')->count(),
            'Pages (total)' => Page::count(),
            'Pages (publiées)' => Page::whereNotNull('published_at')->count(),
            'Dons ce mois' => GivingRecord::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        $lines = ['## Statistiques actuelles'];
        foreach ($stats as $label => $value) {
            $lines[] = "- {$label} : {$value}";
        }

        return implode("\n", $lines);
    }

    private function recentContent(): string
    {
        $lines = [];

        // Recent sermons
        $sermons = Sermon::latest('date')->limit(5)->get(['title', 'speaker', 'date']);
        if ($sermons->isNotEmpty()) {
            $lines[] = '## Dernières prédications';
            foreach ($sermons as $sermon) {
                $date = $sermon->date ? Carbon::parse($sermon->date)->format('d/m/Y') : '';
                $lines[] = "- {$sermon->title} — {$sermon->speaker} ({$date})";
            }
        }

        // Upcoming events
        $events = Event::where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get(['title', 'start_at']);
        if ($events->isNotEmpty()) {
            $lines[] = '## Événements à venir';
            foreach ($events as $event) {
                $date = $event->start_at ? Carbon::parse($event->start_at)->format('d/m/Y H:i') : '';
                $lines[] = "- {$event->title} ({$date})";
            }
        }

        // Active announcements
        $announcements = Announcement::where(function ($q): void {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(5)
            ->get(['title']);
        if ($announcements->isNotEmpty()) {
            $lines[] = '## Annonces actives';
            foreach ($announcements as $announcement) {
                $lines[] = "- {$announcement->title}";
            }
        }

        return implode("\n", $lines);
    }

    private function skillsPrompt(string $locale): string
    {
        $skills = $this->skillRegistry->all();
        if ($skills->isEmpty()) {
            return '';
        }

        $lines = $locale === 'fr'
            ? ["## Compétences disponibles\nTu as les compétences suivantes. L'utilisateur peut les invoquer avec /slug :"]
            : ["## Available Skills\nYou have the following skills. Users can invoke them with /slug:"];

        foreach ($skills as $skill) {
            $lines[] = "- /{$skill->slug()} — {$skill->description($locale)}";
        }

        return implode("\n", $lines);
    }

    private function securityRules(string $locale): string
    {
        if ($locale === 'fr') {
            return <<<'RULES'
## Règles de sécurité
- Ne JAMAIS révéler de numéros de téléphone, adresses email ou montants de dons individuels
- Ne JAMAIS mentionner de données d'autres églises ou tenants
- Utiliser uniquement des données agrégées pour les statistiques financières
- En cas de doute sur la confidentialité, s'abstenir de partager l'information
RULES;
        }

        return <<<'RULES'
## Security Rules
- NEVER reveal phone numbers, email addresses, or individual giving amounts
- NEVER mention data from other churches or tenants
- Only use aggregate data for financial statistics
- When in doubt about confidentiality, withhold the information
RULES;
    }
}
