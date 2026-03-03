<div
    class="flex h-[calc(100vh-7rem)] gap-4"
    x-data="{
        showSidebar: true,
        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('chat-messages');
                if (container) container.scrollTop = container.scrollHeight;
            });
        }
    }"
    x-init="scrollToBottom()"
>
    {{-- Sidebar: Conversations --}}
    <div
        x-show="showSidebar"
        x-transition
        class="w-72 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
    >
        <div class="flex items-center justify-between border-b border-gray-200 p-3 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('ai.conversations') }}
            </h3>
            <button
                wire:click="newConversation"
                class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-primary-700"
            >
                <x-filament::icon icon="heroicon-m-plus" class="h-3.5 w-3.5" />
                {{ __('ai.new_conversation') }}
            </button>
        </div>

        <div class="overflow-y-auto p-2" style="max-height: calc(100% - 52px);">
            @forelse ($conversations as $conv)
                <div
                    wire:key="conv-{{ $conv['id'] }}"
                    class="group flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm transition
                        {{ $conversationId === $conv['id'] ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}"
                    wire:click="selectConversation({{ $conv['id'] }})"
                >
                    <span class="truncate">{{ $conv['title'] }}</span>
                    <button
                        wire:click.stop="deleteConversation({{ $conv['id'] }})"
                        class="ml-2 hidden text-gray-400 transition hover:text-red-500 group-hover:block"
                    >
                        <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @empty
                <p class="px-3 py-4 text-center text-xs text-gray-400">
                    {{ __('ai.no_conversations') }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- Main Chat Area --}}
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <button
                @click="showSidebar = !showSidebar"
                class="text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300"
            >
                <x-filament::icon icon="heroicon-m-bars-3" class="h-5 w-5" />
            </button>
            <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-primary-500" />
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ __('ai.assistant_title') }}
            </h2>
        </div>

        {{-- Messages --}}
        <div
            id="chat-messages"
            class="flex-1 space-y-4 overflow-y-auto p-4"
            wire:poll.visible.5s
        >
            @if (empty($messages) && $streamingContent === '')
                {{-- Welcome state --}}
                <div class="flex h-full flex-col items-center justify-center text-center">
                    <x-filament::icon icon="heroicon-o-sparkles" class="mb-4 h-12 w-12 text-primary-300" />
                    <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ __('ai.welcome_title') }}
                    </h3>
                    <p class="mb-6 max-w-md text-sm text-gray-500 dark:text-gray-400">
                        {{ __('ai.welcome_description') }}
                    </p>

                    {{-- Skill quick actions --}}
                    <div class="flex max-w-lg flex-wrap justify-center gap-2">
                        @foreach (array_slice($skills, 0, 6) as $skill)
                            <button
                                wire:click="$set('message', '/{{ $skill['slug'] }} ')"
                                class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-primary-700 dark:hover:bg-primary-900/30"
                            >
                                /{{ $skill['slug'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                @foreach ($messages as $msg)
                    <div
                        wire:key="msg-{{ $msg['id'] }}"
                        class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}"
                    >
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 text-sm
                            {{ $msg['role'] === 'user'
                                ? 'bg-primary-600 text-white'
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}
                        ">
                            @if ($msg['role'] === 'assistant')
                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                                </div>
                            @else
                                {{ $msg['content'] }}
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Streaming message --}}
                @if ($streamingContent !== '')
                    <div class="flex justify-start" x-init="scrollToBottom()">
                        <div class="max-w-[80%] rounded-2xl bg-gray-100 px-4 py-3 text-sm text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($streamingContent) !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Typing indicator --}}
                @if ($isProcessing && $streamingContent === '')
                    <div class="flex justify-start">
                        <div class="rounded-2xl bg-gray-100 px-4 py-3 dark:bg-gray-800">
                            <div class="flex space-x-1.5">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 0ms"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 150ms"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Input Area --}}
        <div class="border-t border-gray-200 p-4 dark:border-gray-700">
            <form wire:submit="sendMessage" class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea
                        wire:model="message"
                        placeholder="{{ __('ai.input_placeholder') }}"
                        rows="1"
                        class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder-gray-400 transition focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-primary-500 dark:focus:bg-gray-900"
                        x-data="{
                            resize() {
                                $el.style.height = 'auto';
                                $el.style.height = Math.min($el.scrollHeight, 160) + 'px';
                            }
                        }"
                        x-on:input="resize()"
                        x-on:keydown.enter.prevent="if (!event.shiftKey) { $wire.sendMessage(); }"
                        @disabled($isProcessing)
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-primary-600 p-3 text-white transition hover:bg-primary-700 disabled:opacity-50"
                    @disabled($isProcessing || $message === '')
                >
                    @if ($isProcessing)
                        <x-filament::icon icon="heroicon-m-arrow-path" class="h-5 w-5 animate-spin" />
                    @else
                        <x-filament::icon icon="heroicon-m-paper-airplane" class="h-5 w-5" />
                    @endif
                </button>
            </form>
        </div>
    </div>
</div>
