@props([
    'title' => 'Upcoming Events',
    'limit' => 3,
    'showPast' => false
])

@php
    $query = \App\Models\Event::query();
    
    if (!$showPast) {
        $query->where('start_at', '>=', now());
    }
    
    $events = $query->orderBy('start_at', 'asc')->limit($limit)->get();
@endphp

<section class="py-24 bg-slate-50 lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    {{ $title }}
                </h2>
                <p class="mt-4 text-lg text-slate-600">
                    Connect with our community through these upcoming gatherings and activities.
                </p>
            </div>
            <a href="#" class="inline-flex items-center px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-900 font-bold hover:bg-slate-50 transition-colors shadow-sm">
                Full Calendar
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </a>
        </div>

        @if($events->isEmpty())
            <div class="p-16 text-center bg-white rounded-3xl border border-slate-100 shadow-sm">
                <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 rotate-3">
                    <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">No upcoming events</h3>
                <p class="text-slate-500">We're currently planning our next gatherings. Please check back soon!</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($events as $event)
                    <div class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="relative aspect-video overflow-hidden bg-slate-200">
                            @if($event->image)
                                <img src="{{ $event->image }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 opacity-80">
                                    <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            
                            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-sm rounded-2xl p-2 text-center shadow-lg min-w-[60px]">
                                <span class="block text-xl font-black text-slate-900 leading-none">{{ $event->start_at->format('d') }}</span>
                                <span class="block text-[10px] font-bold text-indigo-600 uppercase tracking-tighter mt-1">{{ $event->start_at->format('M') }}</span>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors mb-3">
                                {{ $event->title }}
                            </h3>
                            
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-slate-500">
                                    <svg class="mr-2 w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $event->start_at->format('H:i') }} @if($event->end_at) - {{ $event->end_at->format('H:i') }} @endif
                                </div>
                                <div class="flex items-center text-sm text-slate-500 line-clamp-1">
                                    <svg class="mr-2 w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $event->location }}
                                </div>
                            </div>

                            <a href="#" class="block w-full text-center py-3 bg-slate-50 text-slate-900 font-bold rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                Event Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
