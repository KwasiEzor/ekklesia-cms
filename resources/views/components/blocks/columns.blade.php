@props([
    'layout' => '50-50',
    'columns' => []
])

@php
    $gridClasses = match($layout) {
        '50-50' => 'md:grid-cols-2',
        '33-33-33' => 'md:grid-cols-3',
        '70-30' => 'md:grid-cols-[2fr,1fr]',
        '30-70' => 'md:grid-cols-[1fr,2fr]',
        default => 'md:grid-cols-2',
    };
@endphp

<section class="py-12 bg-white lg:py-16">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div @class(['grid gap-12 items-start', $gridClasses])>
            @foreach($columns as $column)
                <div class="space-y-6">
                    @if(!empty($column['content']))
                        <x-render-blocks :blocks="$column['content']" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
