@props(['body'])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-8">
    <article class="prose prose-lg prose-indigo max-w-none text-slate-700">
        {!! Illuminate\Support\Str::markdown($body) !!}
    </article>
</div>
