@props(['level' => 'h2', 'content'])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-8">
    @if($level === 'h2')
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $content }}</h2>
    @elseif($level === 'h3')
        <h3 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $content }}</h3>
    @else
        <h4 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $content }}</h4>
    @endif
</div>
