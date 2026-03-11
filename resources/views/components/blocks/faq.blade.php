@props(['items'])

<section class="py-24 bg-slate-50 lg:py-32">
    <div class="px-4 mx-auto max-w-3xl sm:px-6 lg:px-8">
        <h2 class="mb-12 text-3xl font-extrabold text-center text-slate-900 sm:text-4xl">
            {{ __('pages.blocks.faq') }}
        </h2>
        
        <div class="space-y-4" x-data="{ active: null }">
            @foreach($items as $index => $item)
                <div class="overflow-hidden bg-white border border-slate-200 rounded-xl shadow-sm">
                    <button 
                        @click="active = (active === {{ $index }} ? null : {{ $index }})"
                        class="flex items-center justify-between w-full px-6 py-4 text-left focus:outline-none"
                    >
                        <span class="text-lg font-semibold text-slate-900">{{ $item['question'] }}</span>
                        <svg 
                            class="w-5 h-5 text-slate-500 transition-transform duration-300" 
                            :class="{ 'rotate-180': active === {{ $index }} }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div 
                        x-show="active === {{ $index }}" 
                        x-collapse
                        x-cloak
                        class="px-6 pb-4 text-slate-600"
                    >
                        {{ $item['answer'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
