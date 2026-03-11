@props([
    'style' => 'solid'
])

<div @class([
    'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8' => $style !== 'waves',
    'w-full' => $style === 'waves'
])>
    @if($style === 'solid')
        <hr class="border-t border-slate-200">
    @elseif($style === 'dashed')
        <hr class="border-t border-dashed border-slate-300">
    @elseif($style === 'gradient')
        <div class="h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent"></div>
    @elseif($style === 'waves')
        <div class="relative h-20 w-full overflow-hidden">
            <svg class="absolute bottom-0 left-0 w-full h-full" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#f8fafc" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,192C384,171,480,117,576,112C672,107,768,149,864,165.3C960,181,1056,171,1152,144C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    @endif
</div>
