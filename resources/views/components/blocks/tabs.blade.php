@props([
    'items' => []
])

<section class="py-12 bg-white lg:py-16">
    <div class="px-4 mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div x-data="{ activeTab: 0 }" class="w-full">
            <!-- Tab Headers -->
            <div class="flex flex-wrap border-b border-slate-200">
                @foreach($items as $index => $item)
                    <button 
                        @click="activeTab = {{ $index }}"
                        :class="activeTab === {{ $index }} ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-all duration-200 whitespace-nowrap"
                    >
                        {{ $item['title'] }}
                    </button>
                @endforeach
            </div>

            <!-- Tab Panels -->
            <div class="mt-8">
                @foreach($items as $index => $item)
                    <div 
                        x-show="activeTab === {{ $index }}"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="prose prose-slate max-w-none prose-indigo prose-lg"
                        style="display: {{ $index === 0 ? 'block' : 'none' }}"
                    >
                        {!! \Illuminate\Support\Str::markdown($item['content']) !!}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
