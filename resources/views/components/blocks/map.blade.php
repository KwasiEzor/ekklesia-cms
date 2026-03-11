@props([
    'title' => 'Our Location',
    'campusId' => null,
    'address' => null
])

@php
    $campus = $campusId ? \App\Models\Campus::find($campusId) : null;
    $displayAddress = $campus ? $campus->address : $address;
    $encodedAddress = urlencode($displayAddress);
@endphp

<section class="py-16 bg-slate-50 overflow-hidden">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-center">
            <div class="lg:col-span-1 text-center lg:text-left">
                <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl mb-6">
                    {{ $title }}
                </h2>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="text-left">
                            <h4 class="font-bold text-slate-900">Visit Us</h4>
                            <p class="text-slate-600 mt-1 leading-relaxed">
                                {!! nl2br(e($displayAddress)) !!}
                            </p>
                        </div>
                    </div>

                    @if($campus && $campus->email)
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="text-left">
                                <h4 class="font-bold text-slate-900">Email Us</h4>
                                <p class="text-slate-600 mt-1">{{ $campus->email }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="pt-6">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $encodedAddress }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200">
                            Get Directions
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="relative w-full h-[400px] sm:h-[500px] rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                    @if($encodedAddress)
                        <iframe 
                            class="w-full h-full"
                            style="border:0"
                            loading="lazy"
                            allowfullscreen
                            src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google_maps.key', 'YOUR_KEY_HERE') }}&q={{ $encodedAddress }}"
                        ></iframe>
                    @else
                        <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400">
                            No address provided for map.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
