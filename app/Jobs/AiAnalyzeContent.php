<?php

namespace App\Jobs;

use App\Services\Ai\AiManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AiAnalyzeContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 1;

    public function __construct(
        private Model $model,
        private string $analysisType = 'general',
        private string $tenantId = '',
    ) {
        $this->queue = 'ai';
        $this->tenantId = $model->tenant_id ?? '';
    }

    public function handle(AiManager $aiManager): void
    {
        $tenant = \App\Models\Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        tenancy()->initialize($tenant);

        $systemPrompt = $this->buildAnalysisPrompt();
        $contentText = $this->extractContent();

        if ($contentText === '') {
            return;
        }

        try {
            $driver = $aiManager->driver();
            $response = $driver->chat(
                system: $systemPrompt,
                messages: [['role' => 'user', 'content' => $contentText]],
            );

            // Store analysis in custom_fields
            $customFields = $this->model->custom_fields ?? [];
            $customFields['_ai_analysis'] = [
                'type' => $this->analysisType,
                'content' => $response->content,
                'model' => $response->model,
                'analyzed_at' => now()->toIso8601String(),
            ];

            $this->model->update(['custom_fields' => $customFields]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function buildAnalysisPrompt(): string
    {
        $type = class_basename($this->model);

        return match ($this->analysisType) {
            'seo' => "Tu es un expert SEO. Analyse le contenu {$type} suivant et propose des améliorations SEO : titre optimisé, méta-description, mots-clés suggérés. Réponds en français.",
            'translation' => "Tu es un traducteur professionnel. Le contenu suivant est en français. Propose une traduction en anglais fidèle au sens et au ton. Préserve le vocabulaire ecclésial.",
            default => "Tu es un assistant éditorial pour une église. Analyse le contenu {$type} suivant et propose des améliorations : clarté, engagement, pertinence. Réponds en français de manière concise.",
        };
    }

    private function extractContent(): string
    {
        $parts = [];

        if (isset($this->model->title)) {
            $parts[] = "Titre : {$this->model->title}";
        }
        if (isset($this->model->body)) {
            $parts[] = "Corps : {$this->model->body}";
        }
        if (isset($this->model->content)) {
            $parts[] = "Contenu : " . (is_array($this->model->content) ? json_encode($this->model->content) : $this->model->content);
        }
        if (isset($this->model->transcript)) {
            $parts[] = "Transcription : {$this->model->transcript}";
        }
        if (isset($this->model->description)) {
            $parts[] = "Description : {$this->model->description}";
        }

        return implode("\n\n", $parts);
    }
}
