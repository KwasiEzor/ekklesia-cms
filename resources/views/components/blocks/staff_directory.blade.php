@props([
    'title' => 'Our Leadership',
    'department' => 'all'
])

@php
    $query = \App\Models\User::query();
    
    // In a real scenario, we would filter by 'staff' role or a custom 'department' attribute
    // For now, we'll pull a few users to demonstrate the block
    $staff = $query->limit(6)->get();
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
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&size=256&background=random" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-900">{{ $member->name }}</h3>
                    <p class="text-indigo-600 font-semibold uppercase tracking-widest text-xs mt-1">
                        {{-- Fallback role if not set --}}
                        {{ $member->role ?? 'Ministry Leader' }}
                    </p>
                    
                    <div class="mt-4 flex justify-center space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="#" class="text-slate-400 hover:text-indigo-600"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="#" class="text-slate-400 hover:text-indigo-600"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.332 3.608 1.308.975.975 1.245 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.332 2.633-1.308 3.608-.975.975-2.242 1.245-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.332-3.608-1.308-.975-.975-1.245-2.242-1.308-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.332-2.633 1.308-3.608.975-.975 2.242-1.245 3.608-1.308 1.266-.058 1.646-.07 4.85-.07m0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-4.718 4.416-4.919 4.919-.058 1.281-.072 1.688-.072 4.947s.014 3.668.072 4.948c.201 5.003 4.561 4.719 4.919 4.918 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 4.717-4.416 4.919-4.919.058-1.281.072-1.688.072-4.947s-.014-3.668-.072-4.948c-.201-5.003-4.561-4.719-4.919-4.918-1.281-.058-1.689-.072-4.948-.072zM12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                        <a href="#" class="text-slate-400 hover:text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
