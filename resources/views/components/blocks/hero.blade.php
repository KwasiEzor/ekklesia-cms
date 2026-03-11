@props([
    'slides' => [],
    'isCarousel' => false,
    'autoplaySpeed' => 5000,
    'transitionType' => 'fade',
    // Support legacy props for single slide if provided
    'title' => null,
    'subtitle' => null,
    'imageUrl' => null,
    'ctaLabel' => null,
    'ctaUrl' => null
])

@php
    // Normalize slides for backward compatibility
    $allSlides = $slides;
    if (empty($allSlides) && $title) {
        $allSlides[] = [
            'title' => $title,
            'subtitle' => $subtitle,
            'image_url' => $imageUrl,
            'cta_label' => $ctaLabel,
            'cta_url' => $ctaUrl,
        ];
    }
@endphp

<section 
    class="relative overflow-hidden bg-slate-900"
    @if($isCarousel && count($allSlides) > 1)
        x-data="{ 
            activeSlide: 0, 
            slidesCount: {{ count($allSlides) }},
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
    @endif
>
    <div class="relative min-h-[600px] flex items-center">
        @foreach($allSlides as $index => $slide)
            <div 
                x-show="activeSlide === {{ $index }}"
                @if($transitionType === 'slide')
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-full"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-full"
                @else
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 transform scale-105"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                @endif
                class="absolute inset-0 w-full h-full"
                style="display: {{ $index === 0 ? 'block' : 'none' }}"
            >
                <!-- Background Image -->
                <div class="absolute inset-0 z-0">
                    <img src="{{ $slide['image_url'] }}" alt="{{ $slide['title'] }}" class="object-cover w-full h-full opacity-50">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 h-full flex items-center">
                    <div class="max-w-3xl py-24 lg:py-32">
                        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-7xl">
                            {{ $slide['title'] }}
                        </h1>
                        
                        @if($slide['subtitle'] ?? null)
                            <p class="mt-6 text-xl text-slate-300 max-w-2xl">
                                {{ $slide['subtitle'] }}
                            </p>
                        @endif
                        
                        @if(($slide['cta_label'] ?? null) && ($slide['cta_url'] ?? null))
                            <div class="mt-10">
                                <a href="{{ $slide['cta_url'] }}" class="inline-flex items-center px-8 py-4 text-lg font-bold text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-500/20">
                                    {{ $slide['cta_label'] }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($isCarousel && count($allSlides) > 1)
            <!-- Navigation Arrows -->
            <div class="absolute inset-y-0 left-0 z-20 flex items-center pl-4">
                <button @click="prev()" class="p-2 text-white/50 transition-colors hover:text-white bg-black/10 hover:bg-black/20 rounded-full backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="15 19l-7-7 7-7"></path></svg>
                </button>
            </div>
            <div class="absolute inset-y-0 right-0 z-20 flex items-center pr-4">
                <button @click="next()" class="p-2 text-white/50 transition-colors hover:text-white bg-black/10 hover:bg-black/20 rounded-full backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7"></path></svg>
                </button>
            </div>

            <!-- Dots -->
            <div class="absolute bottom-8 left-0 right-0 z-20 flex justify-center space-x-3">
                @foreach($allSlides as $index => $slide)
                    <button 
                        @click="activeSlide = {{ $index }}" 
                        :class="activeSlide === {{ $index }} ? 'bg-white w-8' : 'bg-white/30 w-2 hover:bg-white/50'"
                        class="h-2 rounded-full transition-all duration-300"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </div>
</section>
