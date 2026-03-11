@props([
    'title' => 'Our Leadership',
    'department' => 'all'
])

@php
    $query = \App\Models\User::query();
    
    // Filter by department if specified
    if ($department !== 'all') {
        $query->where('department', $department);
    } else {
        // Only show staff (anyone who isn't just a member)
        // For simplicity in the demo, we'll pull users who have a title set
        // or specifically filter by roles if we want to be strict.
        $query->where(function($q) {
            $q->whereNotNull('title')
              ->orWhereNotNull('department');
        });
    }
    
    $staff = $query->limit(12)->get();
@endphp

<section class="py-24 bg-white lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                {{ $title }}
            </h2>
            <div class="mt-4 flex justify-center">
                <div class="h-1.5 w-20 bg-indigo-600 rounded-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($staff as $member)
                <div class="group text-center">
                    <div class="relative mb-6 inline-block">
                        <div class="absolute inset-0 bg-indigo-600 rounded-2xl rotate-3 group-hover:rotate-6 transition-transform"></div>
                        <div class="relative w-48 h-56 bg-slate-200 rounded-2xl overflow-hidden shadow-lg group-hover:-translate-y-2 transition-transform">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&size=256&background=6366f1&color=fff" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-900">{{ $member->name }}</h3>
                    <p class="text-indigo-600 font-semibold uppercase tracking-widest text-xs mt-1">
                        {{ $member->title ?? $member->roles->first()?->name ?? 'Ministry Leader' }}
                    </p>

                    @if($member->bio)
                        <p class="mt-4 text-slate-600 text-sm line-clamp-3 px-4">
                            {{ $member->bio }}
                        </p>
                    @endif
                    
                    @if($member->social_links)
                        <div class="mt-6 flex justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            @foreach($member->social_links as $link)
                                <a href="{{ $link['url'] }}" target="_blank" class="text-slate-400 hover:text-indigo-600 transition-colors">
                                    @switch($link['platform'])
                                        @case('facebook')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
                                            @break
                                        @case('twitter')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                            @break
                                        @case('instagram')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.332 3.608 1.308.975.975 1.245 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.332 2.633-1.308 3.608-.975.975-2.242 1.245-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.332-3.608-1.308-.975-.975-1.245-2.242-1.308-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.332-2.633 1.308-3.608.975-.975 2.242-1.245 3.608-1.308 1.266-.058 1.646-.07 4.85-.07m0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-4.718 4.416-4.919 4.919-.058 1.281-.072 1.688-.072 4.947s.014 3.668.072 4.948c.201 5.003 4.561 4.719 4.919 4.918 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 4.717-4.416 4.919-4.919.058-1.281.072-1.688.072-4.947s-.014-3.668-.072-4.948c-.201-5.003-4.561-4.719-4.919-4.918-1.281-.058-1.689-.072-4.948-.072zM12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                            @break
                                        @case('youtube')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                                            @break
                                        @case('linkedin')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                            @break
                                        @case('website')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                            @break
                                    @endswitch
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
