@props(['items'])

<section class="py-24 bg-white lg:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $item)
                <div class="p-8 transition-all bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md">
                    <div class="flex items-center gap-4 mb-6">
                        @if($item['avatar_url'] ?? null)
                            <img src="{{ $item['avatar_url'] }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="flex items-center justify-center w-12 h-12 text-indigo-600 bg-indigo-100 rounded-full font-bold">
                                {{ substr($item['name'], 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-slate-900">{{ $item['name'] }}</h4>
                            @if($item['role'] ?? null)
                                <p class="text-xs text-indigo-600 uppercase tracking-wider font-semibold">{{ $item['role'] }}</p>
                            @endif
                        </div>
                    </div>
                    <p class="text-slate-600 leading-relaxed italic">
                        &ldquo;{{ $item['content'] }}&rdquo;
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
