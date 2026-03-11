@props(['title', 'subtitle' => null, 'imageUrl', 'ctaLabel' => null, 'ctaUrl' => null])

<section class="relative py-24 overflow-hidden bg-slate-900 lg:py-32">
    <div class="absolute inset-0 z-0">
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="object-cover w-full h-full opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40"></div>
    </div>
    
    <div class="relative z-10 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>
            
            @if($subtitle)
                <p class="mt-6 text-xl text-slate-300">
                    {{ $subtitle }}
                </p>
            @endif
            
            @if($ctaLabel && $ctaUrl)
                <div class="mt-10">
                    <a href="{{ $ctaUrl }}" class="inline-flex items-center px-6 py-3 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $ctaLabel }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
