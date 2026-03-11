@props([
    'title' => null,
    'galleryId' => null,
    'layout' => 'grid'
])

@php
    $gallery = $galleryId ? \App\Models\Gallery::find($galleryId) : null;
    $photos = $gallery ? $gallery->photos : [];
@endphp

<section class="py-24 bg-white lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if($title || ($gallery && $gallery->title))
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $title ?: ($gallery ? $gallery->title : '') }}
                </h2>
                @if($gallery && $gallery->description)
                    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
                        {{ $gallery->description }}
                    </p>
                @endif
            </div>
        @endif

        @if(empty($photos))
            <div class="p-12 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <p class="text-slate-500">No photos found in this gallery.</p>
            </div>
        @else
            <div 
                x-data="{ 
                    showLightbox: false, 
                    activePhoto: null,
                    open(photo) {
                        this.activePhoto = photo;
                        this.showLightbox = true;
                        document.body.classList.add('overflow-hidden');
                    },
                    close() {
                        this.showLightbox = false;
                        document.body.classList.remove('overflow-hidden');
                    }
                }"
            >
                @if($layout === 'slider')
                    <div 
                        x-data="{ 
                            activeSlide: 0, 
                            slidesCount: {{ count($photos) }},
                            next() { this.activeSlide = (this.activeSlide + 1) % this.slidesCount },
                            prev() { this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount }
                        }"
                        class="relative group"
                    >
                        <div class="relative aspect-[16/9] overflow-hidden rounded-3xl bg-slate-100">
                            @foreach($photos as $index => $photo)
                                <img 
                                    x-show="activeSlide === {{ $index }}"
                                    x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 scale-105"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    src="{{ $photo['url'] }}" 
                                    alt="{{ $photo['name'] }}"
                                    class="absolute inset-0 w-full h-full object-cover cursor-pointer"
                                    @click="open({{ json_encode($photo) }})"
                                    style="display: {{ $index === 0 ? 'block' : 'none' }}"
                                >
                            @endforeach
                        </div>
                        
                        <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                @else
                    <div @class([
                        'grid gap-6',
                        'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' => $layout === 'grid',
                        'columns-2 md:columns-3 lg:columns-4 space-y-6' => $layout === 'masonry',
                    ])>
                        @foreach($photos as $photo)
                            <div 
                                class="relative overflow-hidden rounded-2xl bg-slate-100 cursor-pointer group break-inside-avoid"
                                @click="open({{ json_encode($photo) }})"
                            >
                                <img 
                                    src="{{ $photo['medium'] }}" 
                                    alt="{{ $photo['name'] }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                >
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Lightbox -->
                <div 
                    x-show="showLightbox" 
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 p-4"
                    @keydown.escape.window="close()"
                    style="display: none"
                >
                    <button @click="close()" class="absolute top-6 right-6 text-white/70 hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    
                    <div class="max-w-5xl max-h-full">
                        <img :src="activePhoto?.url" :alt="activePhoto?.name" class="max-w-full max-h-[85vh] object-contain rounded-lg">
                        <p x-text="activePhoto?.name" class="mt-4 text-center text-white/80 font-medium"></p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
