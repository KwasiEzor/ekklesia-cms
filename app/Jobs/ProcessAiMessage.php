<?php

namespace App\Jobs;

use App\Events\AiResponseChunk;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiManager;
use App\Services\Ai\SkillRegistry;
use App\Services\TenantContextBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class ProcessAiMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    public function __construct(
        private int $conversationId,
        private int $userId,
        private string $userMessage,
        private string $tenantId,
    ) {}

    public function handle(
        AiManager $aiManager,
        TenantContextBuilder $contextBuilder,
        SkillRegistry $skillRegistry,
    ): void {
        // Initialize tenant context
        $tenant = \App\Models\Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }

        $rateLimitKey = 'ai-messages:tenant:'.$this->tenantId;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) { // 10 messages per minute per tenant
            $conversation = AiConversation::find($this->conversationId);
            if ($conversation) {
                $errorMessage = __('ai.rate_limit_exceeded') !== 'ai.rate_limit_exceeded'
                    ? __('ai.rate_limit_exceeded')
                    : 'Limite de messages atteinte. Veuillez patienter une minute.';

                $conversation->messages()->create([
                    'tenant_id' => $this->tenantId,
                    'role' => 'assistant',
                    'content' => $errorMessage,
                ]);

                broadcast(new AiResponseChunk(
                    userId: $this->userId,
                    conversationId: $conversation->id,
                    chunk: $errorMessage,
                    isComplete: true,
                ));
            }

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        tenancy()->initialize($tenant);

        $conversation = AiConversation::find($this->conversationId);
        if (! $conversation) {
            return;
        }

        // Build system prompt with skill detection
        $skill = $skillRegistry->detectSkill($this->userMessage);
        $systemPrompt = $contextBuilder->buildSystemPrompt($conversation->locale ?? 'fr');

        if ($skill instanceof \App\Services\Ai\AiSkill) {
            $systemPrompt .= "\n\n".$skill->systemPromptAddition();
        }

        // Load conversation history (last 20 messages)
        /** @var \Illuminate\Database\Eloquent\Collection<int, AiMessage> $historyMessages */
        $historyMessages = $conversation->messages()
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->reverse();

        $history = $historyMessages
            ->map(fn (AiMessage $msg): array => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();

        // Get the AI driver and stream response
        $driver = $aiManager->driver();

        try {
            $response = $driver->chatStream(
                system: $systemPrompt,
                messages: $history,
                onChunk: function (string $chunk) use ($conversation): void {
                    broadcast(new AiResponseChunk(
                        userId: $this->userId,
                        conversationId: $conversation->id,
                        chunk: $chunk,
                    ));
                },
            );

            // Store assistant message
            $conversation->messages()->create([
                'tenant_id' => $this->tenantId,
                'role' => 'assistant',
                'content' => $response->content,
                'tokens_input' => $response->tokensInput,
                'tokens_output' => $response->tokensOutput,
                'model' => $response->model,
            ]);

            // Auto-title conversation from first exchange
            if (! $conversation->title && $conversation->messages()->count() <= 2) {
                $title = mb_substr($this->userMessage, 0, 80);
                $conversation->update(['title' => $title]);
            }

            // Broadcast completion
            broadcast(new AiResponseChunk(
                userId: $this->userId,
                conversationId: $conversation->id,
                chunk: '',
                isComplete: true,
                tokensInput: $response->tokensInput,
                tokensOutput: $response->tokensOutput,
            ));
        } catch (\Throwable $e) {
            $errorMessage = __('ai.error_processing');

            // Store error as assistant message
            $conversation->messages()->create([
                'tenant_id' => $this->tenantId,
                'role' => 'assistant',
                'content' => $errorMessage,
                'metadata' => ['error' => $e->getMessage()],
            ]);

            broadcast(new AiResponseChunk(
                userId: $this->userId,
                conversationId: $conversation->id,
                chunk: $errorMessage,
                isComplete: true,
            ));

            report($e);
        }
    }
}
