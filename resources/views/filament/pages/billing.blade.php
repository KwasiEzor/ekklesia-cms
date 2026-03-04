<x-filament-panels::page>
    <div class="ekk-billing space-y-8">
        <section class="ekk-billing-hero rounded-2xl px-6 py-6 lg:px-8 lg:py-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-100/80">
                        Ekklesia Premium
                    </p>
                    <h2 class="text-2xl font-semibold tracking-tight text-white">
                        {{ __('billing.title') }}
                    </h2>
                    <p class="max-w-2xl text-sm text-blue-100/90">
                        {{ __('billing.description') }}
                    </p>
                </div>

                @if($currentPlan)
                    <div class="rounded-xl bg-white/12 px-4 py-3 backdrop-blur-sm ring-1 ring-white/25">
                        <p class="text-xs uppercase tracking-[0.12em] text-blue-100/75">
                            {{ __('billing.current_plan') }}
                        </p>
                        <p class="mt-1 text-lg font-semibold text-white">
                            {{ $currentPlan->name }}
                        </p>
                        <p class="text-sm text-blue-100">
                            {{ $this->formatPlanPrice($currentPlan) }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Section A: Usage Overview --}}
        @if(!empty($usage))
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('billing.usage_overview') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Members Card --}}
                <div class="ekk-billing-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center" style="width:2.5rem;height:2.5rem">
                            <x-heroicon-o-users class="ekk-icon-md text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('billing.members') }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->usageLabel('members') }}</p>
                        </div>
                    </div>
                    @if(($usage['members']['unlimited'] ?? false) === false)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full" style="height:0.5rem">
                            @php $color = $this->usageColor('members'); @endphp
                            <div class="rounded-full transition-all duration-500
                                {{ $color === 'red' ? 'bg-red-500' : ($color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                style="height:0.5rem;width:{{ $this->usagePercent('members') }}%"></div>
                        </div>
                    @else
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">{{ __('billing.unlimited') }}</div>
                    @endif
                </div>

                {{-- Campuses Card --}}
                <div class="ekk-billing-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center" style="width:2.5rem;height:2.5rem">
                            <x-heroicon-o-building-office-2 class="ekk-icon-md text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('billing.campuses') }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->usageLabel('campuses') }}</p>
                        </div>
                    </div>
                    @if(($usage['campuses']['unlimited'] ?? false) === false)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full" style="height:0.5rem">
                            @php $color = $this->usageColor('campuses'); @endphp
                            <div class="rounded-full transition-all duration-500
                                {{ $color === 'red' ? 'bg-red-500' : ($color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                style="height:0.5rem;width:{{ $this->usagePercent('campuses') }}%"></div>
                        </div>
                    @else
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">{{ __('billing.unlimited') }}</div>
                    @endif
                </div>

                {{-- Storage Card --}}
                <div class="ekk-billing-card rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center" style="width:2.5rem;height:2.5rem">
                            <x-heroicon-o-circle-stack class="ekk-icon-md text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('billing.storage') }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->usageLabel('storage_mb') }}</p>
                        </div>
                    </div>
                    @if(($usage['storage_mb']['unlimited'] ?? false) === false)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full" style="height:0.5rem">
                            @php $color = $this->usageColor('storage_mb'); @endphp
                            <div class="rounded-full transition-all duration-500
                                {{ $color === 'red' ? 'bg-red-500' : ($color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                style="height:0.5rem;width:{{ $this->usagePercent('storage_mb') }}%"></div>
                        </div>
                    @else
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">{{ __('billing.unlimited') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Section B: Current Plan + Features --}}
        @if($currentPlan)
        <div class="ekk-billing-card rounded-xl p-6">
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('billing.your_plan') }}</h3>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold
                    {{ $this->planBgAccent($currentPlanSlug) }} text-white">
                    {{ $currentPlan->name }}
                </span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ $currentPlan->formatted_price }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                {{-- Payments --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2
                    {{ $currentPlan->has_payments ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @if($currentPlan->has_payments)
                        <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    @else
                        <x-heroicon-s-x-circle class="ekk-icon-md text-gray-400" />
                    @endif
                    <span class="text-sm font-medium {{ $currentPlan->has_payments ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('billing.feature_payments') }}
                    </span>
                </div>

                {{-- SMS --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2
                    {{ $currentPlan->has_sms ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @if($currentPlan->has_sms)
                        <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    @else
                        <x-heroicon-s-x-circle class="ekk-icon-md text-gray-400" />
                    @endif
                    <span class="text-sm font-medium {{ $currentPlan->has_sms ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('billing.feature_sms') }}
                    </span>
                </div>

                {{-- WhatsApp --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2
                    {{ $currentPlan->has_whatsapp ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @if($currentPlan->has_whatsapp)
                        <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    @else
                        <x-heroicon-s-x-circle class="ekk-icon-md text-gray-400" />
                    @endif
                    <span class="text-sm font-medium {{ $currentPlan->has_whatsapp ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('billing.feature_whatsapp') }}
                    </span>
                </div>

                {{-- AI --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2
                    {{ $currentPlan->has_ai ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @if($currentPlan->has_ai)
                        <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    @else
                        <x-heroicon-s-x-circle class="ekk-icon-md text-gray-400" />
                    @endif
                    <span class="text-sm font-medium {{ $currentPlan->has_ai ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('billing.feature_ai') }}
                    </span>
                </div>

                {{-- Multi-Campus --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2
                    {{ ($currentPlan->max_campuses === 0 || $currentPlan->max_campuses > 1) ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                    @if($currentPlan->max_campuses === 0 || $currentPlan->max_campuses > 1)
                        <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    @else
                        <x-heroicon-s-x-circle class="ekk-icon-md text-gray-400" />
                    @endif
                    <span class="text-sm font-medium {{ ($currentPlan->max_campuses === 0 || $currentPlan->max_campuses > 1) ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('billing.multi_campus') }}
                    </span>
                </div>

                {{-- Storage --}}
                <div class="flex items-center gap-2 rounded-lg px-3 py-2 bg-emerald-50 dark:bg-emerald-500/10">
                    <x-heroicon-s-check-circle class="ekk-icon-md text-emerald-500" />
                    <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                        {{ $this->formatPlanLimit($currentPlan, 'max_storage_mb') }} {{ __('billing.storage') }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Section C: Pricing Cards --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('billing.available_plans') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $plan->plan_slug === $currentPlanSlug;
                        $borderColor = $this->planBorderColor($plan->plan_slug);
                        $isUnlimitedMembers = $plan->isUnlimited('max_members');
                        $isUnlimitedCampuses = $plan->isUnlimited('max_campuses');
                    @endphp
                    <div class="relative rounded-xl bg-white dark:bg-gray-900 shadow-sm
                        border-t-4 {{ $borderColor }}
                        {{ $isCurrent ? 'ring-2 ring-indigo-500 dark:ring-indigo-400' : 'ring-1 ring-gray-950/5 dark:ring-white/10' }}
                        hover:shadow-md transition-shadow duration-200 flex flex-col">

                        {{-- Popular badge --}}
                        @if($plan->plan_slug === 'premium')
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-500 text-white uppercase tracking-wide">
                                    {{ __('billing.popular') }}
                                </span>
                            </div>
                        @endif

                        {{-- Current plan badge --}}
                        @if($isCurrent)
                            <div class="absolute -top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">
                                    {{ __('billing.current') }}
                                </span>
                            </div>
                        @endif

                        <div class="p-6 flex flex-col flex-1">
                            {{-- Plan header --}}
                            <div class="mb-5">
                                <h4 class="text-base font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h4>
                                <div class="mt-2 flex items-baseline gap-1">
                                    <span class="text-3xl font-extrabold text-gray-900 dark:text-white">
                                        {{ $this->formatPlanPrice($plan) }}
                                    </span>
                                    @if($plan->price_cents > 0)
                                        <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ __('billing.per_month') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Limits --}}
                            <div class="space-y-2 mb-5 pb-5 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-users class="ekk-icon-sm text-gray-400" />
                                    @if($isUnlimitedMembers)
                                        <span><strong>{{ __('billing.unlimited') }}</strong> {{ __('billing.members') }}</span>
                                    @else
                                        <span>{{ __('billing.up_to') }} <strong>{{ $this->formatPlanLimit($plan, 'max_members') }}</strong> {{ __('billing.members') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-building-office-2 class="ekk-icon-sm text-gray-400" />
                                    @if($isUnlimitedCampuses)
                                        <span><strong>{{ __('billing.unlimited') }}</strong> {{ __('billing.campuses') }}</span>
                                    @else
                                        <span>{{ __('billing.up_to') }} <strong>{{ $this->formatPlanLimit($plan, 'max_campuses') }}</strong> {{ __('billing.campuses') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-circle-stack class="ekk-icon-sm text-gray-400" />
                                    <span><strong>{{ $this->formatPlanLimit($plan, 'max_storage_mb') }}</strong> {{ __('billing.storage') }}</span>
                                </div>
                            </div>

                            {{-- Features checklist --}}
                            <ul class="space-y-2.5 flex-1">
                                {{-- Payments --}}
                                <li class="flex items-center gap-2 text-sm">
                                    @if($plan->has_payments)
                                        <x-heroicon-s-check class="ekk-icon-sm text-emerald-500" />
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('billing.feature_payments') }}</span>
                                    @else
                                        <x-heroicon-s-x-mark class="ekk-icon-sm text-gray-300 dark:text-gray-600" />
                                        <span class="text-gray-400 dark:text-gray-500">{{ __('billing.feature_payments') }}</span>
                                    @endif
                                </li>

                                {{-- SMS --}}
                                <li class="flex items-center gap-2 text-sm">
                                    @if($plan->has_sms)
                                        <x-heroicon-s-check class="ekk-icon-sm text-emerald-500" />
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('billing.feature_sms') }}</span>
                                    @else
                                        <x-heroicon-s-x-mark class="ekk-icon-sm text-gray-300 dark:text-gray-600" />
                                        <span class="text-gray-400 dark:text-gray-500">{{ __('billing.feature_sms') }}</span>
                                    @endif
                                </li>

                                {{-- WhatsApp --}}
                                <li class="flex items-center gap-2 text-sm">
                                    @if($plan->has_whatsapp)
                                        <x-heroicon-s-check class="ekk-icon-sm text-emerald-500" />
                                        <span class="text-gray-700 dark:text-gray-300">{{ __('billing.feature_whatsapp') }}</span>
                                    @else
                                        <x-heroicon-s-x-mark class="ekk-icon-sm text-gray-300 dark:text-gray-600" />
                                        <span class="text-gray-400 dark:text-gray-500">{{ __('billing.feature_whatsapp') }}</span>
                                    @endif
                                </li>

                                {{-- AI --}}
                                <li class="flex items-center gap-2 text-sm">
                                    @if($plan->has_ai)
                                        <x-heroicon-s-check class="ekk-icon-sm text-emerald-500" />
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ __('billing.feature_ai') }}
                                            @if($plan->ai_tier)
                                                <span class="text-xs text-gray-400">({{ ucfirst($plan->ai_tier) }})</span>
                                            @endif
                                        </span>
                                    @else
                                        <x-heroicon-s-x-mark class="ekk-icon-sm text-gray-300 dark:text-gray-600" />
                                        <span class="text-gray-400 dark:text-gray-500">{{ __('billing.feature_ai') }}</span>
                                    @endif
                                </li>
                            </ul>

                            {{-- Action area --}}
                            @if($plan->plan_slug === 'enterprise')
                                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <a href="mailto:contact@ekklesia.app"
                                       class="block w-full text-center px-4 py-2.5 rounded-lg text-sm font-semibold
                                              bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300
                                              hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                        {{ __('billing.contact_us') }}
                                    </a>
                                </div>
                            @elseif(!$isCurrent)
                                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <span class="block w-full text-center px-4 py-2.5 rounded-lg text-sm font-semibold
                                                 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-default">
                                        {{ __('billing.coming_soon') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>
