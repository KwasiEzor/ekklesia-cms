@props(['url', 'alt' => '', 'caption' => null])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-12">
    <figure class="relative overflow-hidden bg-white border border-slate-100 rounded-3xl shadow-sm">
        <img src="{{ $url }}" alt="{{ $alt }}" class="w-full h-auto object-cover max-h-[70vh]">
        @if($caption)
            <figcaption class="p-6 text-center text-sm italic text-slate-500 bg-slate-50">
                {{ $caption }}
            </figcaption>
        @endif
    </figure>
</div>
