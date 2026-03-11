@props(['title', 'description' => null, 'emailTo' => null])

<section class="py-12 bg-slate-50 lg:py-20">
    <div class="px-4 mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-6 md:p-10 overflow-hidden bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-200/50">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-extrabold text-slate-900">{{ $title }}</h2>
                @if($description)
                    <p class="mt-4 text-lg text-slate-600">{{ $description }}</p>
                @endif
            </div>
            
            <form action="#" method="POST" class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                <div>
                    <label for="first-name" class="block text-sm font-semibold text-slate-900">{{ __('members.first_name') }}</label>
                    <div class="mt-2.5">
                        <input type="text" name="first-name" id="first-name" class="block w-full px-4 py-3 text-slate-900 border-slate-200 rounded-xl shadow-sm focus:ring-indigo-600 focus:border-indigo-600">
                    </div>
                </div>
                <div>
                    <label for="last-name" class="block text-sm font-semibold text-slate-900">{{ __('members.last_name') }}</label>
                    <div class="mt-2.5">
                        <input type="text" name="last-name" id="last-name" class="block w-full px-4 py-3 text-slate-900 border-slate-200 rounded-xl shadow-sm focus:ring-indigo-600 focus:border-indigo-600">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label for="email" class="block text-sm font-semibold text-slate-900">{{ __('members.email') }}</label>
                    <div class="mt-2.5">
                        <input type="email" name="email" id="email" class="block w-full px-4 py-3 text-slate-900 border-slate-200 rounded-xl shadow-sm focus:ring-indigo-600 focus:border-indigo-600">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label for="message" class="block text-sm font-semibold text-slate-900">Message</label>
                    <div class="mt-2.5">
                        <textarea name="message" id="message" rows="4" class="block w-full px-4 py-3 text-slate-900 border-slate-200 rounded-xl shadow-sm focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="block w-full px-8 py-4 text-lg font-bold text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-200">
                        {{ __('common.actions.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
