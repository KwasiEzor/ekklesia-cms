@props(['label', 'url', 'style' => 'primary'])

<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 py-12 text-center">
    <div class="p-12 overflow-hidden bg-white border border-slate-100 rounded-3xl shadow-lg ring-1 ring-slate-900/5">
        <div class="flex flex-col items-center">
            <a href="{{ $url }}" class="{{ $style === 'primary' ? 'inline-flex items-center px-8 py-4 text-lg font-bold text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-700 hover:scale-105 active:scale-95 shadow-xl shadow-indigo-200' : 'inline-flex items-center px-8 py-4 text-lg font-bold text-indigo-600 transition-all border-2 border-indigo-600 rounded-xl hover:bg-indigo-50 hover:scale-105 active:scale-95' }}">
                {{ $label }}
            </a>
        </div>
    </div>
</div>
