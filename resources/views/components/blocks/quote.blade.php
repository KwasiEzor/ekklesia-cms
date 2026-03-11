@props(['text', 'attribution' => null])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-12">
    <blockquote class="relative p-12 overflow-hidden bg-indigo-50 rounded-3xl">
        <svg class="absolute top-0 left-0 w-32 h-32 -translate-x-12 -translate-y-12 text-indigo-100" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true">
            <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
        </svg>
        <div class="relative">
            <p class="text-2xl font-medium leading-9 text-slate-900">
                &ldquo;{{ $text }}&rdquo;
            </p>
            @if($attribution)
                <footer class="mt-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-1 bg-indigo-600 h-8 mr-4"></div>
                        <div class="text-base font-medium text-indigo-600">{{ $attribution }}</div>
                    </div>
                </footer>
            @endif
        </div>
    </blockquote>
</div>
