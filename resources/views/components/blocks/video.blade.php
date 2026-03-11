@props(['url', 'caption' => null])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-12">
    <div class="overflow-hidden bg-black border border-slate-100 rounded-3xl shadow-xl aspect-video">
        @if(str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be'))
            @php
                $videoId = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                    $videoId = $match[1];
                }
            @endphp
            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        @elseif(str_contains($url, 'vimeo.com'))
            @php
                $videoId = '';
                if (preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^\/]*)\/videos\/|album/(?:\d+)/video/|video/|)(\d+)(?:$|/|\?)%i', $url, $match)) {
                    $videoId = $match[1];
                }
            @endphp
            <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $videoId }}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
        @else
            <video class="w-full h-full" controls>
                <source src="{{ $url }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        @endif
    </div>
    @if($caption)
        <p class="mt-4 text-center text-sm italic text-slate-500">{{ $caption }}</p>
    @endif
</div>
