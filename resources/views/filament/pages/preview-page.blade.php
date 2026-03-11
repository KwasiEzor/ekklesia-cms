<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Preview</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full antialiased font-sans text-slate-900">
    <div class="min-h-full">
        <header class="bg-indigo-600 py-2 px-4 text-white text-xs font-bold uppercase tracking-widest text-center sticky top-0 z-50">
            Preview Mode - Draft Content
        </header>

        <main>
            <x-render-blocks :blocks="$blocks" />
        </main>
        
        <footer class="bg-slate-50 border-t border-slate-200 py-12">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 text-center text-slate-500 text-sm">
                &copy; {{ date('Y') }} Ekklesia CMS. All rights reserved.
            </div>
        </footer>
    </div>
</body>
</html>
