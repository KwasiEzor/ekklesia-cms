<div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        <div class="md:flex">
            <!-- Left Side: Info -->
            <div class="bg-indigo-600 md:w-1/3 p-8 text-white">
                <h2 class="text-3xl font-black mb-6">Support Our Mission</h2>
                <p class="text-indigo-100 mb-8 leading-relaxed">
                    Your generosity enables us to continue our work in the community and reach more people with the message of hope.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="bg-indigo-500 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold">Secure Payment</h4>
                            <p class="text-xs text-indigo-200">Encrypted and protected</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="bg-indigo-500 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-12 0 9 9 0 0112 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold">Instant Confirmation</h4>
                            <p class="text-xs text-indigo-200">Receive receipt via email</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="md:w-2/3 p-8 md:p-12">
                @if($errorMessage)
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl flex items-center gap-3 animate-shake">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <p class="text-sm font-medium">{{ $errorMessage }}</p>
                    </div>
                @endif

                <form wire:submit.prevent="donate" class="space-y-6">
                    <!-- Amount Selection -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Select Amount ({{ $currency }})</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([1000, 5000, 10000] as $preset)
                                <button 
                                    type="button" 
                                    wire:click="$set('amount', {{ $preset }})"
                                    class="py-3 px-4 rounded-xl border-2 font-bold transition-all {{ $amount == $preset ? 'border-indigo-600 bg-indigo-50 text-indigo-600' : 'border-slate-100 hover:border-slate-200 text-slate-500' }}"
                                >
                                    {{ number_format($preset) }}
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-3 relative">
                            <input 
                                type="number" 
                                wire:model.live="amount" 
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border-transparent rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all font-bold text-lg"
                                placeholder="Other amount"
                            >
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">{{ $currency }}</span>
                        </div>
                        @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                            <input 
                                type="text" 
                                wire:model="customerName"
                                class="w-full px-4 py-3 bg-slate-50 border-transparent rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all"
                                placeholder="John Doe"
                            >
                            @error('customerName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                            <input 
                                type="email" 
                                wire:model="customerEmail"
                                class="w-full px-4 py-3 bg-slate-50 border-transparent rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all"
                                placeholder="john@example.com"
                            >
                            @error('customerEmail') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Phone (Mobile Money) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Mobile Money Number</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model="phoneNumber"
                                class="w-full px-4 py-3 bg-slate-50 border-transparent rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all"
                                placeholder="+228 00 00 00 00"
                            >
                        </div>
                        @error('phoneNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fund -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Allocate to Fund</label>
                        <select 
                            wire:model="fundId"
                            class="w-full px-4 py-3 bg-slate-50 border-transparent rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all appearance-none"
                        >
                            <option value="">General Offering</option>
                            @foreach($funds as $fund)
                                <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Payment Method</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer group">
                                <input type="radio" wire:model="paymentMethod" value="momo" class="sr-only">
                                <div class="p-4 border-2 rounded-2xl transition-all flex flex-col items-center gap-2 {{ $paymentMethod === 'momo' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-100 hover:border-slate-200' }}">
                                    <svg class="w-8 h-8 {{ $paymentMethod === 'momo' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <span class="text-xs font-bold {{ $paymentMethod === 'momo' ? 'text-indigo-600' : 'text-slate-500' }}">Mobile Money</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer group">
                                <input type="radio" wire:model="paymentMethod" value="card" class="sr-only">
                                <div class="p-4 border-2 rounded-2xl transition-all flex flex-col items-center gap-2 {{ $paymentMethod === 'card' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-100 hover:border-slate-200' }}">
                                    <svg class="w-8 h-8 {{ $paymentMethod === 'card' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    <span class="text-xs font-bold {{ $paymentMethod === 'card' ? 'text-indigo-600' : 'text-slate-500' }}">Credit Card</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-lg shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:hover:translate-y-0"
                    >
                        <span wire:loading.remove>COMPLETE DONATION</span>
                        <span wire:loading>PROCESSING...</span>
                        <svg wire:loading.remove class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        <svg wire:loading class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="mt-8 text-center text-slate-400 text-xs">
        <p>© {{ date('Y') }} {{ tenant('name') }}. All rights reserved.</p>
        <p class="mt-2">Powered by Ekklesia CMS</p>
    </div>
</div>

<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .animate-shake {
        animation: shake 0.4s ease-in-out 0s 2;
    }
</style>
