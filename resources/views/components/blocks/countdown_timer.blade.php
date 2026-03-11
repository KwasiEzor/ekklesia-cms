@props([
    'title' => 'Big Event Starting In...',
    'targetDate',
    'ctaLabel' => null,
    'ctaUrl' => null
])

<section class="py-16 bg-slate-900 overflow-hidden relative">
    <!-- Abstract Shapes -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-500 rounded-full opacity-10 blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full opacity-10 blur-3xl translate-x-1/4 translate-y-1/4"></div>

    <div class="relative z-10 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-white mb-10 sm:text-4xl">
            {{ $title }}
        </h2>

        <div 
            x-data="{
                target: new Date('{{ $targetDate }}').getTime(),
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                isExpired: false,
                update() {
                    let now = new Date().getTime();
                    let distance = this.target - now;
                    
                    if (distance < 0) {
                        this.isExpired = true;
                        return;
                    }
                    
                    this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
                },
                init() {
                    this.update();
                    setInterval(() => this.update(), 1000);
                }
            }"
            class="flex flex-wrap justify-center gap-4 sm:gap-8"
        >
            <template x-if="!isExpired">
                <div class="flex flex-wrap justify-center gap-4 sm:gap-8">
                    <!-- Days -->
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 sm:w-32 sm:h-32 bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl flex items-center justify-center mb-3">
                            <span x-text="days" class="text-3xl sm:text-5xl font-black text-white"></span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-indigo-400 uppercase tracking-widest">Days</span>
                    </div>

                    <!-- Hours -->
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 sm:w-32 sm:h-32 bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl flex items-center justify-center mb-3">
                            <span x-text="hours" class="text-3xl sm:text-5xl font-black text-white"></span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-indigo-400 uppercase tracking-widest">Hours</span>
                    </div>

                    <!-- Minutes -->
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 sm:w-32 sm:h-32 bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl flex items-center justify-center mb-3">
                            <span x-text="minutes" class="text-3xl sm:text-5xl font-black text-white"></span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-indigo-400 uppercase tracking-widest">Minutes</span>
                    </div>

                    <!-- Seconds -->
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 sm:w-32 sm:h-32 bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl flex items-center justify-center mb-3">
                            <span x-text="seconds" class="text-3xl sm:text-5xl font-black text-white"></span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-red-500 uppercase tracking-widest">Seconds</span>
                    </div>
                </div>
            </template>

            <template x-if="isExpired">
                <div class="p-8 bg-indigo-600 rounded-3xl">
                    <p class="text-2xl font-black text-white uppercase tracking-widest">The event has started!</p>
                </div>
            </template>
        </div>

        @if($ctaLabel && $ctaUrl)
            <div class="mt-12">
                <a href="{{ $ctaUrl }}" class="inline-flex items-center px-10 py-4 bg-white text-slate-900 text-lg font-black rounded-2xl hover:bg-indigo-50 transition-all hover:scale-105 shadow-xl shadow-indigo-500/10">
                    {{ $ctaLabel }}
                    <svg class="ml-3 w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
        @endif
    </div>
</section>
