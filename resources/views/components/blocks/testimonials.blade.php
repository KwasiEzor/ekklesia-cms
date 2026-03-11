@props([
    'title' => null, 
    'items' => [], 
    'isCarousel' => false, 
    'autoplaySpeed' => 5000
])

<section class="py-24 bg-slate-50 lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if($title)
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            </div>
        @endif

        @if($isCarousel && count($items) > 1)
            <div 
                x-data="{ 
                    activeSlide: 0, 
                    slidesCount: {{ count($items) }},
                    autoplayInterval: null,
                    init() {
                        this.startAutoplay();
                    },
                    startAutoplay() {
                        this.autoplayInterval = setInterval(() => {
                            this.next();
                        }, {{ $autoplaySpeed }});
                    },
                    stopAutoplay() {
                        clearInterval(this.autoplayInterval);
                    },
                    next() {
                        this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                    },
                    prev() {
                        this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount;
                    }
                }"
                @mouseenter="stopAutoplay()"
                @mouseleave="startAutoplay()"
                class="relative max-w-4xl mx-auto"
            >
                <div class="relative overflow-hidden min-h-[400px]">
                    @foreach($items as $index => $item)
                        <div 
                            x-show="activeSlide === {{ $index }}"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-8"
                            class="absolute inset-0 flex flex-col items-center text-center justify-center p-8 md:p-12 bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-white"
                            style="display: {{ $index === 0 ? 'flex' : 'none' }}"
                        >
                            <div class="mb-8">
                                @if($item['avatar_url'] ?? null)
                                    <img src="{{ $item['avatar_url'] }}" alt="{{ $item['name'] }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-indigo-50 shadow-lg">
                                @else
                                    <div class="flex items-center justify-center w-20 h-20 text-2xl text-indigo-600 bg-indigo-50 rounded-full font-bold ring-4 ring-indigo-50 shadow-lg">
                                        {{ substr($item['name'], 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <blockquote class="text-xl md:text-2xl text-slate-700 italic leading-relaxed mb-8">
                                &ldquo;{{ $item['content'] }}&rdquo;
                            </blockquote>

                            <div>
                                <h4 class="text-lg font-bold text-slate-900">{{ $item['name'] }}</h4>
                                @if($item['role'] ?? null)
                                    <p class="text-sm text-indigo-600 uppercase tracking-widest font-bold mt-1">{{ $item['role'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Navigation -->
                <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 md:-translate-x-12 p-3 text-slate-400 hover:text-indigo-600 transition-colors bg-white rounded-full shadow-lg border border-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="15 19l-7-7 7-7"></path></svg>
                </button>
                <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 md:translate-x-12 p-3 text-slate-400 hover:text-indigo-600 transition-colors bg-white rounded-full shadow-lg border border-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7"></path></svg>
                </button>

                <!-- Dots -->
                <div class="flex justify-center space-x-2 mt-8">
                    @foreach($items as $index => $item)
                        <button 
                            @click="activeSlide = {{ $index }}" 
                            :class="activeSlide === {{ $index }} ? 'bg-indigo-600 w-8' : 'bg-slate-300 w-2 hover:bg-slate-400'"
                            class="h-2 rounded-full transition-all duration-300"
                        ></button>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Grid Mode (Static) -->
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($items as $item)
                    <div class="p-8 transition-all bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-6">
                            @if($item['avatar_url'] ?? null)
                                <img src="{{ $item['avatar_url'] }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-indigo-50">
                            @else
                                <div class="flex items-center justify-center w-12 h-12 text-indigo-600 bg-indigo-50 rounded-full font-bold">
                                    {{ substr($item['name'], 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-slate-900">{{ $item['name'] }}</h4>
                                @if($item['role'] ?? null)
                                    <p class="text-xs text-indigo-600 uppercase tracking-wider font-semibold">{{ $item['role'] }}</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-slate-600 leading-relaxed italic">
                            &ldquo;{{ $item['content'] }}&rdquo;
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
