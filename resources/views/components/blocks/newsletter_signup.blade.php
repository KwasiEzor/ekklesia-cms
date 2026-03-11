@props([
    'title' => 'Stay Updated',
    'description' => 'Subscribe to our newsletter to receive the latest news and updates.',
    'buttonLabel' => 'Subscribe'
])

<section class="py-16 bg-white overflow-hidden">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="relative p-8 md:p-16 bg-slate-900 rounded-3xl overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" fill="none" viewBox="0 0 400 400" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="400" height="400" fill="url(#grid)"/>
                </svg>
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">
                <div class="max-w-xl text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                        {{ $title }}
                    </h2>
                    <p class="mt-4 text-lg text-slate-300">
                        {{ $description }}
                    </p>
                </div>

                <div x-data="{ email: '', success: false }" class="w-full max-w-md">
                    <template x-if="!success">
                        <form @submit.prevent="success = true" class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-grow">
                                <label for="email-address" class="sr-only">Email address</label>
                                <input 
                                    x-model="email"
                                    id="email-address" 
                                    name="email" 
                                    type="email" 
                                    required 
                                    class="w-full px-5 py-4 text-slate-900 placeholder-slate-500 bg-white border-transparent rounded-2xl focus:ring-2 focus:ring-indigo-500 shadow-xl" 
                                    placeholder="your@email.com"
                                >
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20">
                                {{ $buttonLabel }}
                            </button>
                        </form>
                    </template>
                    
                    <template x-if="success">
                        <div class="p-6 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl text-center">
                            <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Thank you!</h3>
                            <p class="text-emerald-100">You've successfully subscribed to our updates.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>
