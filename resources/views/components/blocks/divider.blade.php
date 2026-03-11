@props([
    'style' => 'solid'
])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-8">
    @if($style === 'solid')
        <hr class="border-t border-slate-200">
    @elseif($style === 'dashed')
        <hr class="border-t border-dashed border-slate-300">
    @elseif($style === 'gradient')
        <div class="h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent"></div>
    @endif
</div>
