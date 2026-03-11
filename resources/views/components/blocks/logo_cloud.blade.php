@props([
    'title' => null,
    'logos' => [],
    'isCarousel' => true,
    'autoplaySpeed' => 5000
])

<section class="py-12 bg-slate-50 border-y border-slate-100 overflow-hidden">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if($title)
            <div class="text-center mb-10">
                <h2 class="text-lg font-bold text-slate-500 uppercase tracking-widest">{{ $title }}</h2>
            </div>
        @endif

        @if($isCarousel)
            <div class="relative">
                <!-- Infinite Scroll Wrapper -->
                <div class="flex overflow-hidden group">
                    <div 
                        class="flex space-x-12 animate-marquee py-4 group-hover:pause-marquee"
                        style="animation-duration: {{ max(10, count($logos) * 5) }}s"
                    >
                        @foreach(array_merge($logos, $logos) as $logo)
                            <div class="flex-shrink-0 flex items-center grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                                @if($logo['link'] ?? null)
                                    <a href="{{ $logo['link'] }}" target="_blank">
                                        <img src="{{ $logo['url'] }}" alt="{{ $logo['name'] ?? 'Partner' }}" class="h-12 w-auto object-contain">
                                    </a>
                                @else
                                    <img src="{{ $logo['url'] }}" alt="{{ $logo['name'] ?? 'Partner' }}" class="h-12 w-auto object-contain">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Gradient Overlays -->
                <div class="absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-slate-50 to-transparent z-10"></div>
                <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-slate-50 to-transparent z-10"></div>
            </div>
        @else
            <!-- Static Grid Mode -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center justify-items-center">
                @foreach($logos as $logo)
                    <div class="flex items-center grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                        @if($logo['link'] ?? null)
                            <a href="{{ $logo['link'] }}" target="_blank">
                                <img src="{{ $logo['url'] }}" alt="{{ $logo['name'] ?? 'Partner' }}" class="h-12 w-auto object-contain">
                            </a>
                        @else
                            <img src="{{ $logo['url'] }}" alt="{{ $logo['name'] ?? 'Partner' }}" class="h-12 w-auto object-contain">
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 30s linear infinite;
    }
    .group:hover .group-hover\:pause-marquee {
        animation-play-state: paused;
    }
</style>
