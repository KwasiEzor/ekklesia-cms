<?php

namespace App\Livewire;

use App\Jobs\ProcessAiMessage;
use App\Models\AiConversation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Ai\SkillRegistry;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AiChat extends Component
{
    public string $message = '';

    public ?int $conversationId = null;

    public bool $isProcessing = false;

    public string $streamingContent = '';

    /** @var array<int, array{id: int, role: string, content: string}> */
    public array $messages = [];

    /** @var array<int, array{id: int, title: string}> */
    public array $conversations = [];

    /** @var array<string, array{slug: string, name: string}> */
    public array $skills = [];

    public function mount(): void
    {
        $this->loadConversations();
        $this->loadSkills();

        // Select most recent conversation if available
        if ($this->conversations !== []) {
            $this->selectConversation($this->conversations[0]['id']);
        }
    }

    public function sendMessage(): void
    {
        $content = trim($this->message);
        if ($content === '' || $this->isProcessing) {
            return;
        }

        $this->isProcessing = true;
        $this->streamingContent = '';
        $this->message = '';

        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();
        if (! $user instanceof User || ! $tenant instanceof Tenant) {
            return;
        }

        // Create conversation if needed
        if (! $this->conversationId) {
            $conversation = AiConversation::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'locale' => app()->getLocale(),
            ]);
            $this->conversationId = $conversation->id;
            $this->loadConversations();
        }

        // Store user message
        $conversation = AiConversation::find($this->conversationId);
        if (! $conversation instanceof AiConversation) {
            $this->isProcessing = false;

            return;
        }

        /** @var \App\Models\AiMessage $userMsg */
        $userMsg = $conversation->messages()->create([
            'tenant_id' => $tenant->id,
            'role' => 'user',
            'content' => $content,
        ]);

        $this->messages[] = [
            'id' => $userMsg->id,
            'role' => 'user',
            'content' => $content,
        ];

        // Dispatch job
        ProcessAiMessage::dispatch(
            $this->conversationId,
            $user->id,
            $content,
            $tenant->id,
        );
    }

    #[On('echo-private:ai-chat.{userId},.ai.chunk')]
    public function handleChunk(array $data): void
    {
        if (($data['conversation_id'] ?? null) !== $this->conversationId) {
            return;
        }

        if ($data['is_complete'] ?? false) {
            // Streaming done — add final message
            if ($this->streamingContent !== '') {
                $this->messages[] = [
                    'id' => 0,
                    'role' => 'assistant',
                    'content' => $this->streamingContent,
                ];
            }
            $this->streamingContent = '';
            $this->isProcessing = false;
            $this->loadConversations();

            return;
        }

        $this->streamingContent .= $data['chunk'] ?? '';
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->streamingContent = '';
        $this->isProcessing = false;
    }

    public function selectConversation(int $id): void
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Filament::auth()->id())
            ->first();

        if (! $conversation) {
            return;
        }

        $this->conversationId = $conversation->id;
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\AiMessage> $conversationMessages */
        $conversationMessages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        $this->messages = $conversationMessages
            ->map(fn ($msg): array => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        $this->streamingContent = '';
        $this->isProcessing = false;
    }

    public function deleteConversation(int $id): void
    {
        AiConversation::where('id', $id)
            ->where('user_id', Filament::auth()->id())
            ->delete();

        if ($this->conversationId === $id) {
            $this->newConversation();
        }

        $this->loadConversations();
    }

    public function getUserIdProperty(): int
    {
        return Filament::auth()->id() ?? 0;
    }

    public function render(): View
    {
        return view('livewire.ai-chat');
    }

    private function loadConversations(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, AiConversation> $conversations */
        $conversations = AiConversation::where('user_id', Filament::auth()->id())
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $this->conversations = $conversations
            ->map(fn ($c): array => [
                'id' => $c->id,
                'title' => $c->title ?? __('ai.untitled_conversation'),
            ])
            ->toArray();
    }

    private function loadSkills(): void
    {
        $registry = app(SkillRegistry::class);
        $locale = app()->getLocale();

        $this->skills = $registry->all()
            ->map(fn ($skill): array => [
                'slug' => $skill->slug(),
                'name' => $skill->name($locale),
            ])
            ->values()
            ->toArray();
    }
}
