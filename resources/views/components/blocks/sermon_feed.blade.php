@props([
    'title' => 'Latest Sermons',
    'limit' => 3,
    'seriesId' => null,
    'viewStyle' => 'grid',
    'showDownloadNotes' => false
])

@php
    $query = \App\Models\Sermon::query()->latest('date');
    
    if ($seriesId) {
        $query->where('series_id', $seriesId);
    }
    
    $sermons = $query->limit($limit)->get();
@endphp

<section class="py-24 bg-white lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                {{ $title }}
            </h2>
            <a href="#" class="text-indigo-600 font-bold hover:text-indigo-700 flex items-center gap-2 transition-colors">
                View All 
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        @if($sermons->isEmpty())
            <div class="p-12 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                </div>
                <p class="text-slate-500 font-medium">No sermons found at the moment. Check back later!</p>
            </div>
        @else
            <div @class([
                'grid gap-8',
                'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' => $viewStyle === 'grid',
                'grid-cols-1' => $viewStyle === 'list',
                'grid-cols-1 lg:grid-cols-4' => $viewStyle === 'featured',
            ])>
                @foreach($sermons as $index => $sermon)
                    @php
                        $isFeatured = $viewStyle === 'featured' && $index === 0;
                    @endphp
                    
                    <div @class([
                        'group relative bg-white border border-slate-100 rounded-3xl overflow-hidden transition-all hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-1',
                        'lg:col-span-2 lg:row-span-2' => $isFeatured,
                        'flex flex-col md:flex-row' => $viewStyle === 'list'
                    ])>
                        <!-- Thumbnail Placeholder / Series Image -->
                        <div @class([
                            'relative aspect-video bg-slate-900 overflow-hidden',
                            'md:w-1/3 md:aspect-auto' => $viewStyle === 'list',
                            'aspect-square lg:aspect-video' => $isFeatured
                        ])>
                            <div class="absolute inset-0 flex items-center justify-center bg-indigo-600/10 group-hover:bg-indigo-600/20 transition-colors">
                                <svg class="w-12 h-12 text-indigo-600 transform group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                            </div>
                            
                            @if($sermon->date)
                                <div class="absolute top-4 left-4 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-lg text-xs font-bold text-slate-900 shadow-sm">
                                    {{ $sermon->date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div @class([
                            'p-6 flex flex-col justify-between flex-grow',
                            'p-8 lg:p-10' => $isFeatured,
                            'md:p-8' => $viewStyle === 'list'
                        ])>
                            <div>
                                @if($sermon->series)
                                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-2">
                                        {{ $sermon->series->title }}
                                    </p>
                                @endif
                                
                                <h3 @class([
                                    'font-extrabold text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors',
                                    'text-2xl lg:text-3xl' => $isFeatured,
                                    'text-xl' => !$isFeatured
                                ])>
                                    {{ $sermon->title }}
                                </h3>
                                
                                <div class="flex items-center gap-3 text-slate-500 text-sm mb-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $sermon->speaker }}
                                    </span>
                                    @if($sermon->duration)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $sermon->formatted_duration }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                @if($sermon->video_url)
                                    <button class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-colors">
                                        Watch
                                    </button>
                                @endif
                                @if($sermon->audio_url)
                                    <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition-colors">
                                        Listen
                                    </button>
                                @endif
                                @if(($showDownloadNotes ?? false) && $sermon->notes_url)
                                    <a href="{{ $sermon->notes_url }}" target="_blank" class="px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl hover:bg-indigo-100 transition-colors flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Notes
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
