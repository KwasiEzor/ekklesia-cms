@props([
    'title' => 'Support Our Mission',
    'description' => 'Your generosity helps us reach more people with the message of hope.',
    'buttonLabel' => 'Give Online Now',
    'fundId' => null,
    'showQuickGive' => false,
    'quickGiveAmounts' => '10, 20, 50, 100'
])

@php
    $fund = $fundId ? \App\Models\Fund::find($fundId) : null;
    $amounts = array_map('trim', explode(',', $quickGiveAmounts));
@endphp

<section class="py-16 bg-white">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="relative overflow-hidden bg-indigo-600 rounded-3xl shadow-2xl shadow-indigo-200">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-64 h-64 bg-indigo-500 rounded-full opacity-50 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12 w-64 h-64 bg-indigo-400 rounded-full opacity-30 blur-3xl"></div>
            
            <div class="relative z-10 p-8 md:p-16 flex flex-col lg:flex-row items-center justify-between gap-10">
                <div class="max-w-2xl text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">
                        {{ $title }}
                    </h2>
                    <p class="mt-6 text-xl text-indigo-100">
                        {{ $description }}
                    </p>
                    @if($fund)
                        <div class="mt-4 inline-flex items-center px-3 py-1 bg-indigo-700/50 rounded-full text-xs font-bold text-indigo-100 border border-indigo-400/30">
                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                            Targeting: {{ $fund->name }}
                        </div>
                    @endif

                    @if($showQuickGive && !empty($amounts))
                        <div class="mt-8 flex flex-wrap justify-center lg:justify-start gap-3">
                            @foreach($amounts as $amount)
                                <a href="#" class="px-5 py-2.5 bg-indigo-500/30 hover:bg-indigo-500/50 text-white font-bold rounded-xl border border-indigo-400/30 transition-all hover:scale-105">
                                    ${{ $amount }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <div class="flex-shrink-0">
                    <a href="#" class="inline-flex items-center px-10 py-5 text-xl font-bold text-indigo-600 transition-all bg-white rounded-2xl hover:bg-indigo-50 shadow-xl hover:-translate-y-1">
                        {{ $buttonLabel }}
                        <svg class="ml-3 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
