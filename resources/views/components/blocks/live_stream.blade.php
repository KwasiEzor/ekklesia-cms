@props([
    'title' => 'Join us Live!',
    'streamUrl' => '#',
    'alwaysShow' => true
])

{{-- In a real scenario, we would check a global setting or a LiveStream model status --}}
@php
    $isLive = true; 
@endphp

@if($alwaysShow || $isLive)
<div class="relative bg-slate-900 border-b border-white/10 overflow-hidden">
    <!-- Animated background -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-red-600 via-indigo-600 to-purple-600 animate-gradient-x"></div>
    </div>

    <div class="relative z-10 px-4 py-3 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                <p class="text-sm font-bold text-white uppercase tracking-widest">
                    {{ $title }}
                </p>
            </div>
            
            <div class="flex items-center gap-6">
                <p class="hidden md:block text-indigo-100 text-sm font-medium">
                    Our Sunday Service is currently streaming live. Join our online community!
                </p>
                <a href="{{ $streamUrl }}" target="_blank" class="inline-flex items-center px-4 py-1.5 bg-white text-slate-900 text-xs font-black rounded-full hover:bg-red-50 transition-all hover:scale-105 shadow-lg shadow-white/10">
                    WATCH NOW
                    <svg class="ml-1.5 w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes gradient-x {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    .animate-gradient-x {
        background-size: 200% 200%;
        animation: gradient-x 15s ease infinite;
    }
</style>
@endif
